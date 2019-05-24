<?php

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Error;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Result;
use Bitrix\Main\UserGroupTable;
use Bitrix\Main\UserTable;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\FrontOffice\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Service\UserSearchInterface;
use FourPaws\UserBundle\Service\UserService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsFrontOfficeFestSearchComponent extends \FourPaws\FrontOffice\Bitrix\Component\SubmitForm
{
    /** операции, к одной из которых у пользователя должен быть доступ (по умолчанию) */
    const CAN_ACCESS_USER_OPERATIONS_DEFAULT = [
        'view_subordinate_users',
        'view_all_users',
        'edit_subordinate_users',
    ];

    /** @var array $checkSubordinateOperations */
    protected $checkSubordinateOperations = [
        'view_all_users',
        'edit_all_users'
    ];

    /**
     * FourPawsFrontOfficeFestSearchComponent constructor.
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

        if ($this->request->get('formName') === 'festUserSearch' && $this->request->get('action') === 'userSearch') {
            $action = 'userSearch';
        } elseif ($this->request->get('formName') === 'festUserUpdate' && $this->request->get('action') === 'userUpdate') {
            $action = 'userUpdate';
        }

        return $action;
    }

    /**
     * @throws ApplicationCreateException
     */
    protected function initialLoadAction()
    {
        $this->loadData();
    }

    /**
     * @throws ApplicationCreateException
     */
    protected function userSearchAction()
    {
        $this->initPostFields();

        //if ($this->canEnvUserAccess()) {
            $this->processSearchFormFields();
            $this->obtainUserUpdateForm();
        //}

        $this->loadData();
    }

    /**
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws InvalidIdentifierException
     */
    protected function userUpdateAction()
    {
        $this->initPostFields();

        //if ($this->canEnvUserAccess()) {
            $this->updateFestivalUser();
        //}

        $this->loadData();
    }

    /**
     * @throws InvalidIdentifierException
     */
    protected function updateFestivalUser()
    {
        $this->arResult['IS_UPDATED'] = false;
        $values = $this->arResult['FIELD_VALUES'];
        if ($values['id'] <= 0) {
            throw new InvalidIdentifierException('Неверный id участника фестиваля');
        }

        $fields = [];
        if (array_key_exists('firstName', $values)) {
            $fields['UF_NAME'] = $values['firstName'];
        }
        if (array_key_exists('lastName', $values)) {
            $fields['UF_SURNAME'] = $values['lastName'];
        }
        if (array_key_exists('phone', $values)) {
            $fields['UF_PHONE'] = $values['phone'];
        }
        if (array_key_exists('email', $values)) {
            $fields['UF_EMAIL'] = $values['email'];
        }
        if (array_key_exists('passport', $values)) {
            $fields['UF_PASSPORT'] = $values['passport'];
        }


        if (!$fields['UF_NAME'] || !$fields['UF_PHONE']) {
            $this->arResult['UPDATE_ERROR'] = 'Не переданы обязательные поля';
            return;
        }
        /** @var DataManager $festivalUsersDataManager */
        $festivalUsersDataManager = Application::getInstance()->getContainer()->get('bx.hlblock.festivalusersdata');
        $updateResult = $festivalUsersDataManager::update($values['id'], $fields);
        if (!$updateResult->isSuccess()) {
            $this->arResult['UPDATE_ERROR'] = implode('. ', $updateResult->getErrorMessages());
        } else {
            $this->arResult['IS_UPDATED'] = true;
        }
    }

    /**
     * @throws ApplicationCreateException
     */
    protected function loadData()
    {
        $this->arResult['IS_AUTHORIZED'] = $GLOBALS['USER']->isAuthorized() ? 'Y' : 'N';
        $this->arResult['CAN_ACCESS'] = $this->canEnvUserAccess() ? 'Y' : 'N';
        $this->arResult['ACTION'] = $this->getAction();
        //$this->arResult['IS_AVATAR_AUTHORIZED'] = $this->getUserService()->isAvatarAuthorized() ? 'Y' : 'N';

        $this->includeComponentTemplate();
    }

    /**
     * processSearchFormFields
     */
    protected function processSearchFormFields()
    {
        $fieldName = 'promoId';
        $promoId = $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value !== '' && !preg_match('/^\d+$/', $value)) {
            $this->setFieldError($fieldName, 'Неверно указан ID', 'incorrect_value');
        }

        $fieldName = 'phone';
        $phone = $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value !== '') {
            $phone = $this->cleanPhoneNumberValue($value);
            if ($phone === '') {
                $this->setFieldError($fieldName, 'Номер телефона задан некорректно', 'not_valid');
            }
        }

        $fieldName = 'cardNumber';
        $cardNumber = $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value !== '' && !preg_match('/^\d+$/', $value)) {
            $this->setFieldError($fieldName, 'Номер карты задан некорректно', 'not_valid');
        }

        if (!$promoId && !$phone && !$cardNumber)
        {
            $this->setExecError('', 'Не заполнено ни одно из полей');
        }
    }

    /**
     * Добавление в arResult формы с полями пользователя
     */
    protected function obtainUserUpdateForm()
    {
        $searchResult = null;
        if (empty($this->arResult['ERROR']['FIELD']) && empty($this->arResult['ERROR']['EXEC'])) {
            $searchResult = $this->getParticipantInfoByFormFields();
        }
        if ($searchResult) {
            $this->arResult['USER_INFO'] = $searchResult->getData()['userInfo'];
            if (!$searchResult->isSuccess()) {
                foreach ($searchResult->getErrors() as $error) {
                    $this->setExecError($error->getCode(), $error->getMessage());
                }
            }
        }
    }

    /**
     * @param array $fieldsList
     * @return array
     */
    protected function getFilterByFormFields(array $fieldsList)
    {
        $filter = [];
        foreach ($fieldsList as $fieldName) {
            $value = $this->trimValue($this->getFormFieldValue($fieldName));
            if ($value !== '') {
                $filter[$fieldName] = $value;
            }
        }

        return $filter;
    }

    /**
     * @return Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getParticipantInfoByFormFields()
    {
        $result = new Result();

        $fieldsList = [
            'promoId',
            'cardNumber',
            'phone',
        ];
        $filter = $this->getFilterByFormFields($fieldsList);
        if (empty($filter)) {
            $result->addError(
                new Error('Не заданы параметры поиска', 'emptySearchParams')
            );
        }
        $userInfo = [];
        if ($result->isSuccess()) {
            $userInfo = $this->getParticipantInfoByFilter($filter);
        }

        $result->setData(
            [
                'userInfo' => $userInfo,
            ]
        );

        return $result;
    }

    /**
     * @param array $filter
     * @return array
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getParticipantInfoByFilter($filter): array
    {
        if (!$filter['promoId'] && !$filter['phone'] && !$filter['cardNumber']) {
            return [];
        }

        if ($filter['cardNumber']) {
            $user = $this->searchUserByCardNumber($filter['cardNumber']);
            if (!$user) {
                return [];
            }

            $personalPhone = $user->getPersonalPhone();
            if ($filter['phone'] && $filter['phone'] != $personalPhone) {
                return [];
            }

            $filter['phone'] = $personalPhone;
        }

        $ormFilter = [];
        if ($filter['promoId']) {
            $ormFilter['UF_FESTIVAL_USER_ID'] = $filter['promoId'];
        }
        if ($filter['phone']) {
            $ormFilter['UF_PHONE'] = $filter['phone'];
        }

        if ($filter['promoId'] || $filter['phone']) {
            /** @var DataManager $festivalUsersDataManager */
            $festivalUsersDataManager = Application::getInstance()->getContainer()->get('bx.hlblock.festivalusersdata');
            $searchResult = $festivalUsersDataManager::query()
                ->setFilter($ormFilter)
                ->setSelect(['*'])
                ->setLimit(1)
                ->exec()
                ->fetch();
            return $searchResult ?: [];
        }
    }
}
