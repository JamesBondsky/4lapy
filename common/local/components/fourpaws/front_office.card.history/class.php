<?php

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use FourPaws\App\Application;
//use FourPaws\External\Manzana\Model\Cheque;
//use FourPaws\External\Manzana\Model\ChequePayment;
//use FourPaws\External\Manzana\Model\ChequesByContractContactCheques;
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
        $action = 'initialLoad';
        if ($this->request->get('action') === 'postForm')  {
            if ($this->request->get('formName') === 'cardHistory') {
                $action = 'postForm';
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

        if (empty($this->arResult['ERROR']['FIELD'])) {
            if ($this->trimValue($this->getFormFieldValue('getCardHistory')) === 'Y') {
                $this->obtainCheques();
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
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value === '') {
            $this->setFieldError($fieldName, 'Undefined card number', 'empty');
        } else {
            $continue = true;
            if ($continue) {
                try {
                    // @var FourPaws\External\Manzana\Model\CardValidateResult $validateResult
                    $validateResult = $this->getManzanaService()->validateCardByNumberRaw($value);
                    // 0 - ok; 1 - карта не существует; 2 - карта принадлежит другому юзеру
                    $validationResultCode = intval($validateResult->validationResultCode);
                    if ($validationResultCode === 1) {
                        $this->setFieldError($fieldName, 'Not found', 'not_found');
                    } elseif ($validationResultCode === 2) {
                        // @var FourPaws\External\Manzana\Model\Card $card
                        $card = $this->getManzanaService()->searchCardByNumber($value);
                        if ($card) {
                            $this->arResult['CARD_DATA']['NUMBER'] = $value;
                            $this->arResult['CARD_DATA']['CARD_ID'] = $validateResult->cardId;
                            $this->arResult['CARD_DATA']['CONTACT_ID'] = trim($card->contactId);
                            $this->arResult['CARD_DATA']['CONTACT_CARDS'] = [];

                            $cards = $this->getManzanaService()->getCardsByContactId($this->arResult['CARD_DATA']['CONTACT_ID']);
                            foreach ($cards as $cardsItem) {
                                // @var FourPaws\External\Manzana\Model\CardByContractCards $cardsItem
                                $cardId = trim($cardsItem->cardId);
                                $this->arResult['CARD_DATA']['CONTACT_CARDS'][$cardId] = [
                                    'ID' => $cardId,
                                    'TYPE' => htmlspecialcharsbx(trim($cardsItem->bonusType)),
                                    'TYPE_TEXT' => htmlspecialcharsbx(trim($cardsItem->bonusTypeText)),
                                    'NUMBER' => htmlspecialcharsbx(trim($cardsItem->cardNumber)),
                                    'STATUS' => htmlspecialcharsbx(trim($cardsItem->statusText)),
                                    // Активный баланс (pl_active_balance)
                                    'BALANCE' => doubleval($cardsItem->activeBalance),
                                    // Скидка (pl_discount)
                                    'DISCOUNT' => doubleval($cardsItem->discount),
                                    // Сумма со скидкой (pl_summdiscounted)
                                    'SUMM' => doubleval($cardsItem->sumDiscounted),
                                    // Получено баллов (pl_credit)
                                    'CREDIT' => doubleval($cardsItem->credit),
                                    // Потрачено баллов (pl_debet)
                                    'DEBET' => doubleval($cardsItem->debit),
                                    'IS_CURRENT' => $this->arResult['CARD_DATA']['NUMBER'] === trim($cardsItem->cardNumber) ? 'Y' : 'N',
                                    'IS_BONUS_CARD' => 'N',
                                ];
                                // исправляем тип карты
                                // товарищи из манзаны гарантируют: ненулевой pl_debet означает, что карта бонусная
                                if (doubleval($cardsItem->debit) > 0) {
                                    $this->arResult['CARD_DATA']['CONTACT_CARDS'][$cardId]['IS_BONUS_CARD'] = 'Y';
                                }
                            }
                        } else {
                            $this->setFieldError($fieldName, 'Not found', 'not_found');
                        }
                    } else {
                        $this->setFieldError($fieldName, 'Wrong status', 'wrong_status');
                    }
                } catch (\Exception $exception) {
                    $this->setFieldError($fieldName, $exception->getMessage(), 'exception');

                    $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
                }
            }
        }
    }

    protected function obtainCheques()
    {
        $this->arResult['CHEQUES'] = [];

        $cheques = [];
        try {
            if (!empty($this->arResult['CARD_DATA']['CONTACT_ID'])) {
                $cheques = $this->getManzanaService()->getChequesByContactId($this->arResult['CARD_DATA']['CONTACT_ID']);
            }

            /*
            if (!empty($this->arResult['CARD_DATA']['CARD_ID'])) {
                $cheques = $this->getManzanaService()->getChequesByCardId($this->arResult['CARD_DATA']['CARD_ID']);
            }
            */

            if ($cheques) {
                foreach ($cheques as $cheque) {
                    if ($cheque->hasItems === 2) {
                        $this->arResult['CHEQUES'][] = [
                            'CHEQUE_ID' => htmlspecialcharsbx(trim($cheque->chequeId)),
                            'NUMBER' => htmlspecialcharsbx(trim($cheque->chequeNumber)),
                            'DATE' => $cheque->date,
                            'BUSINESS_UNIT_NAME' => htmlspecialcharsbx(trim($cheque->businessUnitName)),
                            'SUMM_DISCOUNTED' => $cheque->sumDiscounted,
                            'PAID_BY_BONUS' => $cheque->paidByBonus,
                            'BONUS' => $cheque->bonus,
                            'SUMM' => $cheque->sum,
                        ];
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->setExecError('cardHistory', $exception->getMessage(), 'exception');

            $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
        }
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

    protected function setFieldError($fieldName, $errorMsg, $errCode = '')
    {
        $this->arResult['ERROR']['FIELD'][$fieldName] = new Error($errorMsg, $errCode);
        //$this->log()->debug(sprintf('$fieldName: %s; $errorMsg: %s; $errCode: %s', $fieldName, $errorMsg, $errCode));
    }

    protected function setExecError($errName, $errorMsg, $errCode = '')
    {
        $this->arResult['ERROR']['EXEC'][$errName] = new Error($errorMsg, $errCode);
        //$this->log()->debug(sprintf('$fieldName: %s; $errorMsg: %s; $errCode: %s', $fieldName, $errorMsg, $errCode));
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
