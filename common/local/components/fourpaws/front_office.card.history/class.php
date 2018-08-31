<?php

use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\FrontOffice\Bitrix\Component\CustomerRegistration;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsFrontOfficeCardHistoryComponent extends CustomerRegistration
{
    /**
     * FourPawsFrontOfficeCardHistoryComponent constructor.
     *
     * @param null|\CBitrixComponent $component
     */
    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->withLogName(__CLASS__);
    }

    /**
     * @param array $params
     * @return array
     */
    public function onPrepareComponentParams($params)
    {
        $params = parent::onPrepareComponentParams($params);

        return $params;
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

    protected function initialLoadAction()
    {
        $this->loadData();
    }

    /**
     * @throws ApplicationCreateException
     */
    protected function postFormAction()
    {
        $this->initPostFields();

        if ($this->canEnvUserAccess()) {
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
        $this->arResult['CAN_ACCESS'] = $this->canEnvUserAccess() ? 'Y' : 'N';
        $this->arResult['ACTION'] = $this->getAction();
        $this->includeComponentTemplate();
    }

    /**
     * @throws ApplicationCreateException
     */
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
     *
     * @throws ApplicationCreateException
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
     *
     * @throws ApplicationCreateException
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
     *
     * @throws ApplicationCreateException
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
}
