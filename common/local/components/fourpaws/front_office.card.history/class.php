<?php

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use FourPaws\App\Application;
//use FourPaws\External\Manzana\Model\Cheque;
//use FourPaws\External\Manzana\Model\ChequePayment;
//use FourPaws\External\Manzana\Model\ChequesByContractContactCheques;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\External\ManzanaService;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsFrontOfficeCardHistoryComponent extends \CBitrixComponent
{
    use LazyLoggerAwareTrait;

    private $action = '';
    /** @var ManzanaService $manzanaService */
    private $manzanaService;

    public function __construct($component = null)
    {
        // LazyLoggerAwareTrait не умеет присваивать имя по классам без неймспейса
        // делаем это вручную
        $this->logName = __CLASS__;

        parent::__construct($component);
    }

    public function onPrepareComponentParams($params)
    {
        $params['CURRENT_PAGE'] = isset($params['CURRENT_PAGE']) ? trim($params['CURRENT_PAGE']) : '';
        if (!strlen($params['CURRENT_PAGE'])) {
            $params['CURRENT_PAGE'] = $this->request->getRequestedPage();
            // отсечение index.php
            if (substr($params['CURRENT_PAGE'], -10) === '/index.php') {
                $params['CURRENT_PAGE'] = substr($params['CURRENT_PAGE'], 0, -9);
            }
        }

        $this->arResult['ORIGINAL_PARAMETERS'] = $params;

        $params['CACHE_TYPE'] = isset($params['CACHE_TYPE']) ? $params['CACHE_TYPE'] : 'A';
        $params['CACHE_TIME'] = isset($params['CACHE_TIME']) ? $params['CACHE_TIME'] : 3600;

        $params = parent::onPrepareComponentParams($params);

        return $params;
    }

    public function executeComponent()
    {
        try {
            $this->setAction($this->prepareAction());
            $this->doAction();
        } catch (\Exception $exception) {
            $this->log()->critical(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
            throw $exception;
        }
    }

    /**
     * @param string $action
     * @return void
     */
    protected function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    protected function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    protected function prepareAction()
    {
        $this->arResult['CAN_ACCESS'] = $this->checkPermissions() ? 'Y' : 'N';

        $action = 'initialLoad';

        if ($this->arResult['CAN_ACCESS'] === 'Y') {
            if ($this->request->get('action') === 'postForm')  {
                if ($this->request->get('formName') === 'cardHistory') {
                    $action = 'postForm';
                }
            }
        }

        return $action;
    }

    protected function doAction()
    {
        $action = $this->getAction();
        if (is_callable(array($this, $action.'Action'))) {
            call_user_func(array($this, $action.'Action'));
        }
    }

    protected function checkPermissions()
    {
        $result = true;

        return $result;
    }

    /**
     * @return ManzanaService
     */
    protected function getManzanaService()
    {
        if (!$this->manzanaService) {
            $this->manzanaService = Application::getInstance()->getContainer()->get('manzana.service');
        }
        return $this->manzanaService;
    }

    protected function initialLoadAction()
    {
        $this->loadData();
    }

    protected function postFormAction()
    {
        $this->initPostFields();

        $this->processCardNumber();

        if (!$this->isErrors()) {
            // получение списка карт по id контакта
            $cardsResult = null;
            if ($this->getFormFieldValue('getContactCards') === 'Y') {
                $cardsResult = $this->getCardsByContactId($this->arResult['CARD_DATA']['CONTACT_ID']);
            }
            if ($cardsResult) {
                $this->arResult['CONTACT_CARDS'] = $cardsResult->getData()['cards'];
                if (!$cardsResult->isSuccess()) {
                    $this->setExecError('getContactCards', $cardsResult->getErrors(), 'runtime');
                }
            }

            // получение списка чеков по id контакта или id карты
            $chequesResult = null;
            if ($this->getFormFieldValue('getContactCheques') === 'Y') {
                $chequesResult = $this->getChequesByContactId($this->arResult['CARD_DATA']['CONTACT_ID']);
            } elseif ($this->getFormFieldValue('getCardCheques') === 'Y') {
                $chequesResult = $this->getChequesByCardId($this->arResult['CARD_DATA']['CARD_ID']);
            }
            if ($chequesResult) {
                $this->arResult['CHEQUES'] = $chequesResult->getData()['cheques'];
                if (!$chequesResult->isSuccess()) {
                    $this->setExecError('getCheques', $chequesResult->getErrors(), 'runtime');
                }
            }

            // получение детализации чека по его id
            $chequeItemsResult = null;
            if ($this->getFormFieldValue('getChequeItems') === 'Y') {
                $chequeId = (int) $this->getFormFieldValue('chequeId');
                // to do
            }
            if ($chequeItemsResult) {
                $this->arResult['CHEQUE_ITEMS'] = $chequeItemsResult->getData()['items'];
                if (!$cardsResult->isSuccess()) {
                    $this->setExecError('getContactCards', $cardsResult->getErrors(), 'runtime');
                }
            }

        }

        $this->loadData();
    }

    protected function loadData()
    {
        $this->arResult['ACTION'] = $this->getAction();
        $this->includeComponentTemplate();
    }

    protected function initPostFields()
    {
        $this->arResult['~FIELD_VALUES'] = $this->request->getPostList()->toArray();
        $this->arResult['FIELD_VALUES'] = $this->walkRequestValues($this->arResult['~FIELD_VALUES']);
    }

    protected function processCardNumber()
    {
        $fieldName = 'cardNumberForHistory';
        $cardNumber = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($cardNumber === '') {
            $this->setFieldError($fieldName, 'Номер карты не задан', 'empty');
        } elseif (strlen($cardNumber) != 13) {
            $this->setFieldError($fieldName, 'Неверный номер карты', 'incorrect_value');
        } else {
            $validateResult = $this->validateCardByNumber($cardNumber);
            $validateResultData = $validateResult->getData();
            if (!$validateResult->isSuccess()) {
                $this->setFieldError($fieldName, $validateResult->getErrors(), 'runtime');
            } elseif (empty($validateResultData['validate'])) {
                $this->setFieldError($fieldName, 'Карта не найдена', 'not_found');
            }

            if ($validateResultData['validate']) {
                // 0 - ok; 1 - карта не существует; 2 - карта принадлежит другому юзеру
                if ($validateResultData['validate']['VALIDATION_RESULT_CODE'] === 2) {
                    $searchCardResult = $this->searchCardByNumber($cardNumber);
                    $searchCardResultData = $searchCardResult->getData();
                    if (!$searchCardResult->isSuccess()) {
                        $this->setFieldError($fieldName, $searchCardResult->getErrors(), 'runtime');
                    } elseif (empty($searchCardResultData['card'])) {
                        $this->setFieldError($fieldName, 'Карта не найдена', 'not_found');
                    } else {
                        $this->arResult['CARD_DATA']['NUMBER'] = $cardNumber;
                        $this->arResult['CARD_DATA']['CARD_ID'] = $validateResultData['validate']['CARD_ID'];
                        $this->arResult['CARD_DATA']['CONTACT_ID'] = $searchCardResultData['card']['CONTACT_ID'];
                    }
                } elseif ($validateResultData['validate']['VALIDATION_RESULT_CODE'] === 1) {
                    $this->setFieldError($fieldName, 'Not found', 'not_found');
                } else {
                    $this->setFieldError($fieldName, 'Wrong status', 'wrong_status');
                }
            }
        }
    }

    public function validateCardByNumber(string $cardNumber)
    {
        $result = new Result();

        if ($cardNumber === '') {
            $result->addError(
                new Error('Не задан номер карты', 'emptyCardNumber')
            );
        }

        $validateRaw = null;
        if ($result->isSuccess()) {
            try {
                /** @var FourPaws\External\Manzana\Model\CardValidateResult $validateRaw */
                $validateRaw = $this->getManzanaService()->validateCardByNumberRaw($cardNumber);
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'validateCardByNumberRawException')
                );

                $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
            }
        }

        $validate = [];
        if ($validateRaw) {
            $validate['CARD_ID'] = trim($validateRaw->cardId);
            $validate['IS_VALID'] = trim($validateRaw->isValid);
            $validate['FIRST_NAME'] = trim($validateRaw->firstName);
            $validate['VALIDATION_RESULT'] = trim($validateRaw->validationResult);
            // 0 - ok; 1 - карта не существует; 2 - карта принадлежит другому юзеру
            $validate['VALIDATION_RESULT_CODE'] = (int) $validateRaw->validationResultCode;
        }

        $result->setData(
            [
                'validate' => $validate,
                'validateRaw' => $validateRaw,
            ]
        );

        return $result;
    }

    public function searchCardByNumber(string $cardNumber)
    {
        $result = new Result();

        if ($cardNumber === '') {
            $result->addError(
                new Error('Не задан номер карты', 'emptyCardNumber')
            );
        }

        $cardRaw = null;
        if ($result->isSuccess()) {
            try {
                /** @var FourPaws\External\Manzana\Model\Card $cardRaw */
                $cardRaw = $this->getManzanaService()->searchCardByNumber($cardNumber);
            } catch (CardNotFoundException $exception) {
                $result->addError(
                    new Error('Карта не найдена', 'not_found')
                );
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'searchCardByNumberException')
                );

                $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
            }
        }

        $card = [];
        if ($cardRaw) {
            $card['CONTACT_ID'] = trim($cardRaw->contactId);
        }

        $result->setData(
            [
                'card' => $card,
                'cardRaw' => $cardRaw,
            ]
        );

        return $result;
    }

    public function getCardsByContactId(string $contactId)
    {
        $result = new Result();

        if ($contactId === '') {
            $result->addError(
                new Error('Не задан id контакта', 'emptyContactId')
            );
        }

        $cardsRaw = [];
        if ($result->isSuccess()) {
            try {
                $cardsRaw = $this->getManzanaService()->getCardsByContactId($contactId);
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'getCardsByContactIdException')
                );

                $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
            }
        }

        $cards = [];
        foreach ($cardsRaw as $cardsItem) {
            /** @var FourPaws\External\Manzana\Model\CardByContractCards $cardsItem */
            $cardId = trim($cardsItem->cardId);
            $cards[$cardId] = [
                'ID' => $cardId,
                'TYPE' => trim($cardsItem->bonusType),
                'TYPE_TEXT' => trim($cardsItem->bonusTypeText),
                'NUMBER' => trim($cardsItem->cardNumber),
                'STATUS' => trim($cardsItem->statusText),
                // Активный баланс (pl_active_balance)
                'BALANCE' => (double) $cardsItem->activeBalance,
                // Скидка (pl_discount)
                'DISCOUNT' => (double) $cardsItem->discount,
                // Сумма со скидкой (pl_summdiscounted)
                'SUMM' => (double) $cardsItem->sumDiscounted,
                // Получено баллов (pl_credit)
                'CREDIT' => (double) $cardsItem->credit,
                // Потрачено баллов (pl_debet)
                'DEBET' => (double) $cardsItem->debit,
                'IS_BONUS_CARD' => 'N',
            ];
            // исправляем тип карты
            // товарищи из манзаны гарантируют: ненулевой pl_debet означает, что карта бонусная
            if ((double) $cardsItem->debit > 0) {
                $cards[$cardId]['IS_BONUS_CARD'] = 'Y';
            }
        }

        $result->setData(
            [
                'cards' => $cards,
                'cardsRaw' => $cardsRaw,
            ]
        );

        return $result;
    }

    public function getChequesByContactId(string $contactId)
    {
        $result = new Result();

        if ($contactId === '') {
            $result->addError(
                new Error('Не задан id контакта', 'emptyContactId')
            );
        }

        $chequesRaw = [];
        if ($result->isSuccess()) {
            try {
                $chequesRaw = $this->getManzanaService()->getChequesByContactId($contactId);
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'getChequesByContactIdException')
                );

                $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
            }
        }

        $cheques = [];
        if ($chequesRaw) {
            foreach ($chequesRaw as $cheque) {
                if ($cheque->hasItems === 2) {
                    $cheques[] = [
                        'CHEQUE_ID' => trim($cheque->chequeId),
                        'NUMBER' => trim($cheque->chequeNumber),
                        'DATE' => $cheque->date,
                        'BUSINESS_UNIT_NAME' => trim($cheque->businessUnitName),
                        'SUM_DISCOUNTED' => $cheque->sumDiscounted,
                        'PAID_BY_BONUS' => $cheque->paidByBonus,
                        'BONUS' => $cheque->bonus,
                        'SUM' => $cheque->sum,
                    ];
                }
            }
        }

        $result->setData(
            [
                'cheques' => $cheques,
                'chequesRaw' => $chequesRaw,
            ]
        );

        return $result;
    }

    public function getChequesByCardId(string $cardId)
    {
        $result = new Result();

        if ($cardId === '') {
            $result->addError(
                new Error('Не задан id карты', 'emptyCardId')
            );
        }

        $chequesRaw = [];
        if ($result->isSuccess()) {
            try {
                $chequesRaw = $this->getManzanaService()->getChequesByCardId($cardId);
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'getChequesByCardIdException')
                );

                $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
            }
        }

        $cheques = [];
        if ($chequesRaw) {
            foreach ($chequesRaw as $cheque) {
                if ($cheque->hasItems === 2) {
                    $cheques[] = [
                        'CHEQUE_ID' => trim($cheque->chequeId),
                        'NUMBER' => trim($cheque->chequeNumber),
                        'DATE' => $cheque->date,
                        'BUSINESS_UNIT_CODE' => trim($cheque->businessUnitCode),
                        'SUM_DISCOUNTED' => $cheque->sumDiscounted,
                        'PAID_BY_BONUS' => $cheque->paidByBonus,
                        'BONUS' => $cheque->bonus,
                        'SUM' => $cheque->sum,
                    ];
                }
            }
        }

        $result->setData(
            [
                'cheques' => $cheques,
                'chequesRaw' => $chequesRaw,
            ]
        );

        return $result;
    }

    protected function getFormFieldValue($fieldName, $getSafeValue = false)
    {
        $key = $getSafeValue ? 'FIELD_VALUES' : '~FIELD_VALUES';
        return isset($this->arResult[$key][$fieldName]) ? $this->arResult[$key][$fieldName] : null;
    }

    protected function trimValue($value)
    {
        if (is_null($value)) {
            return '';
        }
        return is_scalar($value) ? trim($value) : '';
    }

    protected function prepareErrorMsg($errorMsg)
    {
        // стоит ли здесь делать htmlspecialcharsbx(), вот в чем вопрос...
        $result = '';
        if (is_array($errorMsg)) {
            $result = [];
            foreach ($errorMsg as $item) {
                if ($item instanceof Error) {
                    $result[] = '['.$item->getCode().'] '.$item->getMessage();
                } elseif (is_scalar($item)) {
                    $result[] = $item;
                }
            }
            $result = implode('<br>', $result);
        } elseif (is_scalar($errorMsg)) {
            $result = $errorMsg;
        }
        return $result;
    }

    protected function setFieldError($fieldName, $errorMsg, $errCode = '')
    {
        $errorMsg = $this->prepareErrorMsg($errorMsg);
        $this->arResult['ERROR']['FIELD'][$fieldName] = new Error($errorMsg, $errCode);
        //$this->log()->debug(sprintf('$fieldName: %s; $errorMsg: %s; $errCode: %s', $fieldName, $errorMsg, $errCode));
    }

    protected function setExecError($errName, $errorMsg, $errCode = '')
    {
        $errorMsg = $this->prepareErrorMsg($errorMsg);
        $this->arResult['ERROR']['EXEC'][$errName] = new Error($errorMsg, $errCode);
        //$this->log()->debug(sprintf('$fieldName: %s; $errorMsg: %s; $errCode: %s', $fieldName, $errorMsg, $errCode));
    }

    protected function isErrors()
    {
        return !empty($this->arResult['ERROR']);
    }

    protected function walkRequestValues($value)
    {
        if (is_scalar($value)) {
            return htmlspecialcharsbx($value);
        } elseif (is_array($value)) {
            return array_map(
                [$this, __FUNCTION__],
                $value
            );
        }
        return $value;
    }
}
