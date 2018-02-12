<?php

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Adv\Bitrixtools\Tools\Main\UserGroupUtils;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use FourPaws\App\Application;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\External\ManzanaService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsFrontOfficeCardHistoryComponent extends \CBitrixComponent
{
    use LazyLoggerAwareTrait;

    /** код группы пользователей, имеющих доступ к компоненту, если ничего не задано в параметрах подключения */
    const DEFAULT_USER_GROUP_CODE = 'FRONT_OFFICE_USERS';
    const BX_ADMIN_GROUP_ID = 1;

    /** @var string $action */
    private $action = '';
    /** @var ManzanaService $manzanaService */
    private $manzanaService;
    /** @var UserService $userCurrentUserService */
    private $userCurrentUserService;
    /** @var string $canAccess */
    protected $canAccess = '';
    /** @var array $userGroups */
    private $userGroups;
    /** @var bool $isUserAdmin */
    private $isUserAdmin;

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

        $params['USER_ID'] = isset($params['USER_ID']) ? (int)$params['USER_ID'] : 0;
        if ($params['USER_ID'] <= 0) {
            $params['USER_ID'] = (int)$GLOBALS['USER']->getId();
        }

        // группы пользователей, имеющих доступ к функционалу
        $params['USER_GROUPS'] = isset($params['USER_GROUPS']) && is_array($params['USER_GROUPS']) ? $params['USER_GROUPS'] : [];
        if (empty($params['USER_GROUPS'])) {
            try {
                $defaultGroupId = UserGroupUtils::getGroupIdByCode(static::DEFAULT_USER_GROUP_CODE);
                if ($defaultGroupId) {
                    $params['USER_GROUPS'][] = $defaultGroupId;
                }
            } catch (\Exception $exception) {}
        }

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
     * @return array
     */
    protected function getUserGroups()
    {
        if (!isset($this->userGroups)) {
            $this->userGroups = [];
            try {
                if ($this->arParams['USER_ID']) {
                    $this->userGroups = $this->getUserService()->getUserGroups($this->arParams['USER_ID']);
                }
            } catch (\Exception $exception) {}
            // группа "все пользователи"
            $this->userGroups[] = 2;
            $this->userGroups = array_unique($this->userGroups);
        }

        return $this->userGroups;
    }

    /**
     * @return bool
     */
    protected function isUserAdmin()
    {
        if (!isset($this->isUserAdmin)) {
            $this->isUserAdmin = in_array(static::BX_ADMIN_GROUP_ID, $this->getUserGroups());
        }

        return $this->isUserAdmin;
    }

    /**
     * @return bool
     */
    protected function canAccess()
    {
        if ($this->canAccess === '') {
            $this->canAccess = 'N';

            if ($this->isUserAdmin()) {
                $this->canAccess = 'Y';
            } else {
                $userGroups = $this->getUserGroups();
                $canAccessGroups = array_merge($this->arParams['USER_GROUPS'], [static::BX_ADMIN_GROUP_ID]);
                if (array_intersect($canAccessGroups, $userGroups)) {
                    $this->canAccess = 'Y';
                }
            }
        }

        return $this->canAccess === 'Y';
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

    /**
     * @return UserService
     */
    public function getUserService()
    {
        if (!$this->userCurrentUserService) {
            $this->userCurrentUserService = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
        }
        return $this->userCurrentUserService;
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

    protected function initialLoadAction()
    {
        $this->loadData();
    }

    protected function postFormAction()
    {
        $this->initPostFields();

        if ($this->canAccess()) {
            $this->processCardNumber();

            // список карт по id контакта
            $this->obtainContactCards();

            // список чеков по id контакта или id карты
            $this->obtainCheques();

            // детализация чека по id
            $this->obtainChequeItems();
        }

        $this->loadData();
    }

    protected function loadData()
    {
        $this->arResult['IS_AUTHORIZED'] = $GLOBALS['USER']->isAuthorized() ? 'Y' : 'N';
        $this->arResult['CAN_ACCESS'] = $this->canAccess() ? 'Y' : 'N';
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
                if ($validateResultData['validate']['_IS_CARD_OWNED_'] === 'Y') {
                    $searchCardResult = $this->searchCardByNumber($cardNumber);
                    $searchCardResultData = $searchCardResult->getData();
                    if (!$searchCardResult->isSuccess()) {
                        $this->setFieldError($fieldName, $searchCardResult->getErrors(), 'runtime');
                    } elseif (empty($searchCardResultData['card'])) {
                        $this->setFieldError($fieldName, 'Карта не найдена', 'not_found');
                    } else {
                        $this->arResult['CARD_DATA'] = $searchCardResultData['card'];
                        $this->arResult['CARD_DATA']['NUMBER'] = $cardNumber;
                        $this->arResult['CARD_DATA']['CARD_ID'] = $validateResultData['validate']['CARD_ID'];
                    }
                } elseif ($validateResultData['validate']['_IS_CARD_NOT_EXISTS_'] === 'Y') {
                    $this->setFieldError($fieldName, 'Not found', 'not_found');
                } else {
                    $this->setFieldError($fieldName, 'Wrong status', 'wrong_status');
                }
            }
        }
    }

    /**
     * Заполнение arResult списком карт по контакту переданной карты
     */
    protected function obtainContactCards()
    {
        if (empty($this->arResult['CARD_DATA'])) {
            return;
        }

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
    }

    /**
     * Заполнение arResult списком чеков по переданной карте
     */
    protected function obtainCheques()
    {
        if (empty($this->arResult['CARD_DATA'])) {
            return;
        }

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
    }

    /**
     * Заполнение arResult данными детализации чека по переданному идентификатору
     */
    protected function obtainChequeItems()
    {
        $chequeItemsResult = null;
        if ($this->getFormFieldValue('getChequeItems') === 'Y') {
            $chequeId = trim($this->getFormFieldValue('chequeId'));
            $chequeItemsResult = $this->getChequeItems($chequeId);
        }
        if ($chequeItemsResult) {
            $this->arResult['CHEQUE_ITEMS'] = $chequeItemsResult->getData()['chequeItems'];
            if (!$chequeItemsResult->isSuccess()) {
                $this->setExecError('getChequeItems', $chequeItemsResult->getErrors(), 'runtime');
            }
        }
    }

    /**
     * @param string $cardNumber
     * @return Result
     */
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
            // 0 - ok; 1 - карта не существует; 2 - карта принадлежит другому клиенту
            $validate['VALIDATION_RESULT_CODE'] = (int)$validateRaw->validationResultCode;
            $validate['_IS_CARD_OWNED_'] = $validateRaw->isCardOwned() ? 'Y' : 'N';
            $validate['_IS_CARD_NOT_EXISTS_'] = $validateRaw->isCardNotExists() ? 'Y' : 'N';
        }

        $result->setData(
            [
                'validate' => $validate,
                'validateRaw' => $validateRaw,
            ]
        );

        return $result;
    }

    /**
     * @param string $cardNumber
     * @return Result
     */
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
            $card['FIRST_NAME'] = trim($cardRaw->firstName);
            $card['SECOND_NAME'] = trim($cardRaw->secondName);
            $card['LAST_NAME'] = trim($cardRaw->lastName);
            $card['BIRTHDAY'] = $cardRaw->birthDate;
            $card['PHONE'] = trim($cardRaw->phone);
            $card['EMAIL'] = trim($cardRaw->email);
        }

        $result->setData(
            [
                'card' => $card,
                'cardRaw' => $cardRaw,
            ]
        );

        return $result;
    }

    /**
     * @param string $contactId
     * @return Result
     */
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
                '_IS_BONUS_CARD_' => 'N',
            ];
            // исправляем тип карты
            // товарищи из манзаны гарантируют: ненулевой pl_debet означает, что карта бонусная
            if ((double)$cardsItem->debit > 0) {
                $cards[$cardId]['_IS_BONUS_CARD_'] = 'Y';
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

    /**
     * @param string $contactId
     * @return Result
     */
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
                if ($cheque->hasItemsBool()) {
                    $cheques[] = [
                        'CHEQUE_ID' => trim($cheque->chequeId),
                        'NUMBER' => trim($cheque->chequeNumber),
                        'DATE' => $cheque->date,
                        'BUSINESS_UNIT_NAME' => trim($cheque->businessUnit),
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

    /**
     * @param string $cardId
     * @return Result
     */
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
                if ($cheque->hasItemsBool()) {
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

    /**
     * @param string $chequeId
     * @return Result
     */
    public function getChequeItems(string $chequeId)
    {
        $result = new Result();

        if ($chequeId === '') {
            $result->addError(
                new Error('Не задан id чека', 'emptyСhequeId')
            );
        }

        $chequeItemsRaw = [];
        if ($result->isSuccess()) {
            try {
                $chequeItemsRaw = $this->getManzanaService()->getItemsByCheque($chequeId);
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'getItemsByChequeException')
                );

                $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
            }
        }

        $chequeItems = [];
        if ($chequeItemsRaw) {
            foreach ($chequeItemsRaw as $chequeItem) {
                $chequeItems[] = [
                    'CHEQUE_ID' => trim($chequeItem->chequeId),
                    'ARTICLE_NAME' => trim($chequeItem->name),
                    'ARTICLE_NUMBER' => trim($chequeItem->number),
                    'QUANTITY' => (double)$chequeItem->quantity,
                    'PRICE' => (double)$chequeItem->price,
                    'DISCOUNT' => (double)$chequeItem->discount,
                    'SUM' => (double)$chequeItem->sum,
                    'SUM_DISCOUNTED' => (double)$chequeItem->sumDiscounted,
                    'URL' => trim($chequeItem->url),
                    'BONUS' => (double)$chequeItem->bonus,
                ];
            }
        }

        $result->setData(
            [
                'chequeItems' => $chequeItems,
                'chequeItemsRaw' => $chequeItemsRaw,
            ]
        );

        return $result;
    }

    /**
     * @param $fieldName
     * @param bool $getSafeValue
     * @return mixed
     */
    protected function getFormFieldValue($fieldName, $getSafeValue = false)
    {
        $key = $getSafeValue ? 'FIELD_VALUES' : '~FIELD_VALUES';
        return isset($this->arResult[$key][$fieldName]) ? $this->arResult[$key][$fieldName] : null;
    }

    /**
     * @param $value
     * @return string
     */
    protected function trimValue($value)
    {
        if (is_null($value)) {
            return '';
        }
        return is_scalar($value) ? trim($value) : '';
    }

    /**
     * @param array|string $errorMsg
     * @return string
     */
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

    /**
     * @param string $fieldName
     * @param array|string $errorMsg
     * @param string $errCode
     */
    protected function setFieldError(string $fieldName, $errorMsg, string $errCode = '')
    {
        $errorMsg = $this->prepareErrorMsg($errorMsg);
        $this->arResult['ERROR']['FIELD'][$fieldName] = new Error($errorMsg, $errCode);
        //$this->log()->debug(sprintf('$fieldName: %s; $errorMsg: %s; $errCode: %s', $fieldName, $errorMsg, $errCode));
    }

    /**
     * @param string $errName
     * @param array|string $errorMsg
     * @param string $errCode
     */
    protected function setExecError(string $errName, $errorMsg, $errCode = '')
    {
        $errorMsg = $this->prepareErrorMsg($errorMsg);
        $this->arResult['ERROR']['EXEC'][$errName] = new Error($errorMsg, $errCode);
        //$this->log()->debug(sprintf('$fieldName: %s; $errorMsg: %s; $errCode: %s', $fieldName, $errorMsg, $errCode));
    }

    /**
     * @return bool
     */
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
