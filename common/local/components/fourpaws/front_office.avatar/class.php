<?php

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UserGroupTable;
use Bitrix\Main\UserTable;
use FourPaws\App\Exceptions\ApplicationCreateException;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsFrontOfficeAvatarComponent extends \FourPaws\FrontOffice\Bitrix\Component\SubmitForm
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
     * FourPawsFrontOfficeAvatarComponent constructor.
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

        if ($this->request->get('formName') === 'avatar') {
            if ($this->request->get('action') === 'postForm') {
                $action = 'postForm';
            } elseif ($this->request->get('action') === 'userAuth') {
                $action = 'userAuth';
            }
        } elseif ($this->request->get('action') === 'forceAuth') {
            $action = 'forceAuth';
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
    protected function postFormAction()
    {
        $this->initPostFields();

        if ($this->canEnvUserAccess()) {
            $this->processSearchFormFields();
            $this->obtainUsersList();
        }

        $this->loadData();
    }

    /**
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function forceAuthAction()
    {
        //$this->initPostFields();
        $this->setFormFieldValue('userId', $this->request->get('userId'));

        if ($this->canEnvUserAccess()) {
            $this->doAuth();
        }

        $this->loadData();
    }

    /**
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function userAuthAction()
    {
        $this->initPostFields();

        if ($this->canEnvUserAccess()) {
            $this->doAuth();
        }

        $this->loadData();
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function doAuth()
    {
        $this->arResult['AUTH_ACTION_SUCCESS'] = 'N';
        $fieldsList = [
            'userId',
        ];
        $filter = $this->getFilterByFormFields($fieldsList);
        if (!empty($filter)) {
            $usersList = $this->getUserListByFilter($filter);
            if ($usersList) {
                $user = reset($usersList);
                $authResult = false;
                try {
                    $authResult = $this->getUserService()->avatarAuthorize($user['ID']);
                } catch (\Exception $exception) {}
                if ($authResult) {
                    $this->arResult['AUTH_ACTION_SUCCESS'] = 'Y';
                } else {
                    $this->setExecError(
                        'authFailed',
                        'Не удалось авторизоваться под указанным пользователем',
                        'authFailed'
                    );
                }
            } else {
                $this->setExecError(
                    'canNotLogin',
                    'Невозможно авторизоваться под указанным пользователем',
                    'canNotLogin'
                );
            }
        } else {
            $this->setExecError(
                'emptyUserId',
                'Не задан идентификатор пользователя',
                'emptyUserId'
            );
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
        $this->arResult['IS_AVATAR_AUTHORIZED'] = $this->getUserService()->isAvatarAuthorized() ? 'Y' : 'N';

        $this->includeComponentTemplate();
    }

    /**
     * processSearchFormFields
     */
    protected function processSearchFormFields()
    {
        $fieldName = 'cardNumber';
        $cardNumber = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($cardNumber !== '' && strlen($cardNumber) != 13) {
            $this->setFieldError($fieldName, 'Неверный номер карты', 'incorrect_value');
        }

        $fieldName = 'birthDay';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value !== '') {
            if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value)) {
                $this->setFieldError($fieldName, 'Значение задано некорректно', 'not_valid');
            } else {
                if (!$GLOBALS['DB']->IsDate($value, 'DD.MM.YYYY')) {
                    $this->setFieldError($fieldName, 'Значение задано некорректно', 'not_valid');
                }
            }
        }

        $fieldName = 'phone';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value !== '') {
            $phone = $this->cleanPhoneNumberValue($value);
            if ($phone === '') {
                $this->setFieldError($fieldName, 'Номер телефона задан некорректно', 'not_valid');
            }
        }
    }

    /**
     * Заполнение arResult списком пользователей по поисковому запросу
     */
    protected function obtainUsersList()
    {
        $searchResult = null;
        if (empty($this->arResult['ERROR']['FIELD']) && $this->getFormFieldValue('getUsersList') === 'Y') {
            $searchResult = $this->getUsersByFormFields();
        }
        if ($searchResult) {
            $this->arResult['USERS_LIST'] = $searchResult->getData()['list'];
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
        //$filterByName = [];
        foreach ($fieldsList as $fieldName) {
            $value = $this->trimValue($this->getFormFieldValue($fieldName));
            if ($value !== '') {
                switch ($fieldName) {
                    case 'cardNumber':
                        // номер карты
                        $filter['=UF_DISCOUNT_CARD'] = $value;
                        break;
                    case 'phone':
                        // телефон
                        $filter['PERSONAL_PHONE'] = $value;
                        $filter['PERSONAL_PHONE_EXACT_MATCH'] = 'Y';
                        break;
                    case 'firstName':
                        // имя
                        $filter['NAME'] = $value;
                        //$filterByName[0] = $value;
                        break;
                    case 'secondName':
                        // отчество
                        $filter['SECOND_NAME'] = $value;
                        $filter['SECOND_NAME_EXACT_MATCH'] = 'Y';
                        //$filterByName[1] = $value;
                        break;
                    case 'lastName':
                        // фамилия
                        $filter['LAST_NAME'] = $value;
                        $filter['LAST_NAME_EXACT_MATCH'] = 'Y';
                        //$filterByName[2] = $value;
                        break;
                    case 'birthDay':
                        // дата рождения
                        $filter['PERSONAL_BIRTHDAY_1'] = $value;
                        $filter['PERSONAL_BIRTHDAY_2'] = $value;
                        break;
                    case 'userId':
                        // id пользователя
                        $filter['ID_EQUAL_EXACT'] = (int)$value;
                        break;
                }
            }
        }
        //if ($filterByName) {
        //    $filter['NAME'] = implode(' & ', $filterByName);
        //}

        if ($filter) {
            $filter['ACTIVE'] = 'Y';
            $filter['!ID'] = $this->arParams['USER_ID'];

            foreach ($this->checkSubordinateOperations as $operation) {
                if (!$this->canEnvUserDoOperation($operation)) {
                    $filter['CHECK_SUBORDINATE'] = $this->getEnvUserSubordinateGroups();
                    break;
                }
            }
            if (!$this->canEnvUserDoOperation('edit_php')) {
                $filter['NOT_ADMIN'] = true;
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
    protected function getUsersByFormFields()
    {
        $result = new Result();

        $fieldsList = [
            'cardNumber', 'phone',
            'firstName', 'secondName', 'lastName',
            'birthDay',
        ];
        $filter = $this->getFilterByFormFields($fieldsList);
        if (empty($filter)) {
            $result->addError(
                new Error('Не заданы параметры поиска', 'emptySearchParams')
            );
        }
        $usersList = [];
        if ($result->isSuccess()) {
            $usersList = $this->getUserListByFilter($filter);
        }

        $result->setData(
            [
                'list' => $usersList,
            ]
        );

        return $result;
    }

    /**
     * @param array $filter
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getUserListByFilter($filter)
    {
        $usersList = [];

        //
        // На сайте в таблице b_user_group могут быть заданы связи юзеров с несуществующими группами,
        // поэтому фильтр CHECK_SUBORDINATE в CUser::GetList будет исключать таких юзеров.
        // Чтобы этого не было, используем самописного монстра:
        //
        $filterOrm = $filter;
        if (isset($filterOrm['PERSONAL_PHONE_EXACT_MATCH'])) {
            $filterOrm['=PERSONAL_PHONE'] = $filterOrm['PERSONAL_PHONE'];
            unset($filterOrm['PERSONAL_PHONE_EXACT_MATCH']);
            unset($filterOrm['PERSONAL_PHONE']);
        }
        if (isset($filterOrm['SECOND_NAME_EXACT_MATCH'])) {
            $filterOrm['=SECOND_NAME'] = $filterOrm['SECOND_NAME'];
            unset($filterOrm['SECOND_NAME_EXACT_MATCH']);
            unset($filterOrm['SECOND_NAME']);
        }
        if (isset($filterOrm['LAST_NAME_EXACT_MATCH'])) {
            $filterOrm['=LAST_NAME'] = $filterOrm['LAST_NAME'];
            unset($filterOrm['LAST_NAME_EXACT_MATCH']);
            unset($filterOrm['LAST_NAME']);
        }
        if (isset($filterOrm['ID_EQUAL_EXACT'])) {
            $filterOrm['=ID'] = $filterOrm['ID_EQUAL_EXACT'];
            unset($filterOrm['ID_EQUAL_EXACT']);
        }
        if (isset($filterOrm['ACTIVE'])) {
            $filterOrm['=ACTIVE'] = $filterOrm['ACTIVE'];
            unset($filterOrm['ACTIVE']);
        }
        if (isset($filterOrm['PERSONAL_BIRTHDAY_1'])) {
            $filterOrm['>=PERSONAL_BIRTHDAY'] = $filterOrm['PERSONAL_BIRTHDAY_1'];
            unset($filterOrm['PERSONAL_BIRTHDAY_1']);
        }
        if (isset($filterOrm['PERSONAL_BIRTHDAY_2'])) {
            $filterOrm['<=PERSONAL_BIRTHDAY'] = $filterOrm['PERSONAL_BIRTHDAY_2'];
            unset($filterOrm['PERSONAL_BIRTHDAY_2']);
        }

        // Основная выборка
        $query = UserTable::query();
        $query->setSelect(
            [
                'ID', 'NAME', 'LAST_NAME', 'SECOND_NAME',
                'EMAIL', 'LOGIN',
                'PERSONAL_PHONE', 'PERSONAL_BIRTHDAY',
                'UF_DISCOUNT_CARD',
            ]
        );

        // для реализации сортировки FULL_NAME CUser::GetList()
        $query->registerRuntimeField(
            null,
            new ExpressionField(
                'ORDER_LAST_NAME_1',
                'IF(%s IS NULL OR %s = \'\', 1, 0)',
                ['LAST_NAME', 'LAST_NAME']
            )
        );
        $query->registerRuntimeField(
            null,
            new ExpressionField(
                'ORDER_LAST_NAME_2',
                'IF(%s IS NULL OR %s = \'\', 1, %s)',
                ['LAST_NAME', 'LAST_NAME', 'LAST_NAME']
            )
        );
        $query->registerRuntimeField(
            null,
            new ExpressionField(
                'ORDER_NAME_1',
                'IF(%s IS NULL OR %s = \'\', 1, 0)',
                ['NAME', 'NAME']
            )
        );
        $query->registerRuntimeField(
            null,
            new ExpressionField(
                'ORDER_NAME_2',
                'IF(%s IS NULL OR %s = \'\', 1, %s)',
                ['NAME', 'NAME', 'NAME']
            )
        );
        $query->registerRuntimeField(
            null,
            new ExpressionField(
                'ORDER_SECOND_NAME_1',
                'IF(%s IS NULL OR %s = \'\', 1, 0)',
                ['SECOND_NAME', 'SECOND_NAME']
            )
        );
        $query->registerRuntimeField(
            null,
            new ExpressionField(
                'ORDER_SECOND_NAME_2',
                'IF(%s IS NULL OR %s = \'\', 1, %s)',
                ['SECOND_NAME', 'SECOND_NAME', 'SECOND_NAME']
            )
        );

        $query->setOrder(
            [
                // сортировка FULL_NAME
                'ORDER_LAST_NAME_1' => 'asc',
                'ORDER_LAST_NAME_2' => 'asc',
                'ORDER_NAME_1' => 'asc',
                'ORDER_NAME_2' => 'asc',
                'ORDER_SECOND_NAME_1' => 'asc',
                'ORDER_SECOND_NAME_2' => 'asc',
                'LOGIN' => 'asc',

                'UF_DISCOUNT_CARD' => 'asc',
                'ID' => 'asc',
            ]
        );

        // Для выборки юзеров с учетом подчиненности генерируем запрос вида:
        // SELECT U.*
        //    FROM
        //    b_user U
        //    WHERE
        //    (
        //      (U.ID=4 OR NOT EXISTS(SELECT 'x' FROM b_user_group UGS WHERE UGS.USER_ID=U.ID AND UGS.GROUP_ID NOT IN (0,2,9)))
        //      AND
        //      (not exists (SELECT * FROM b_user_group UGNA WHERE UGNA.USER_ID=U.ID AND UGNA.GROUP_ID = 1))
        //    )
        if (isset($filterOrm['CHECK_SUBORDINATE'])) {
            $userSubordinateGroups = $filterOrm['CHECK_SUBORDINATE'];
            $userSubordinateGroups[] = 0;
            $userSubordinateGroups = array_unique($userSubordinateGroups);

            // (SELECT 'x' FROM b_user_group UGS WHERE UGS.USER_ID=U.ID AND UGS.GROUP_ID NOT IN (0,2,9))
            $notSubordinatedGroupSubQuery = UserGroupTable::query();
            $notSubordinatedGroupSubQuery->setFilter(
                [
                    '!=GROUP_ID' => $userSubordinateGroups,
                    '=USER_ID' => new SqlExpression('%s'),
                    // добавим еще проверку существования группы
                    '!GROUP.ID' => false,
                ]
            );
            $notSubordinatedGroupSubQuery->setCustomBaseTableAlias('UGS');

            // CASE WHEN NOT EXISTS(SELECT 'x' FROM b_user_group UGS WHERE UGS.USER_ID=U.ID AND UGS.GROUP_ID NOT IN (0,2,9)) THEN 1 ELSE 0 END
            $query->registerRuntimeField(
                null,
                new ExpressionField(
                    'IS_SUBORDINATED_USER',
                    'CASE WHEN NOT EXISTS('.$notSubordinatedGroupSubQuery->getQuery().') THEN 1 ELSE 0 END',
                    ['ID']
                )
            );

            // (U.ID=4 OR NOT EXISTS(SELECT 'x' FROM b_user_group UGS WHERE UGS.USER_ID=U.ID AND UGS.GROUP_ID NOT IN (0,2,9)))
            $query->where(
                Query::filter()
                    //->logic('or')
                    //->where('ID', $ownUserId)
                    ->where('IS_SUBORDINATED_USER', 1)
            );

            unset($filterOrm['CHECK_SUBORDINATE']);
        }

        if (isset($filterOrm['NOT_ADMIN'])) {
            // (SELECT * FROM b_user_group UGNA WHERE UGNA.USER_ID=U.ID AND UGNA.GROUP_ID = 1)
            $groupAdminSubQuery = UserGroupTable::query();
            $groupAdminSubQuery->setFilter(
                [
                    '=GROUP_ID' => 1,
                    '=USER_ID' => new SqlExpression('%s')
                ]
            );
            $groupAdminSubQuery->setCustomBaseTableAlias('UGNA');

            // CASE WHEN NOT EXISTS(SELECT * FROM b_user_group UGNA WHERE UGNA.USER_ID=U.ID AND UGNA.GROUP_ID = 1) THEN 1 ELSE 0 END
            $query->registerRuntimeField(
                null,
                new ExpressionField(
                    'IS_NOT_ADMIN',
                    'CASE WHEN NOT EXISTS('.$groupAdminSubQuery->getQuery().') THEN 1 ELSE 0 END',
                    ['ID']
                )
            );

            // (not exists (SELECT * FROM b_user_group UGNA WHERE UGNA.USER_ID=U.ID AND UGNA.GROUP_ID = 1))
            $query->where('IS_NOT_ADMIN', 1);

            unset($filterOrm['NOT_ADMIN']);
        }

        if ($filterOrm) {
            foreach ($filterOrm as $key => $value) {
                $query->addFilter($key, $value);
            }
        }

        $itemsIterator = $query->exec();
        while ($item = $itemsIterator->fetch()) {
            /** @var \Bitrix\Main\Type\Date $birth */
            $birth = $item['PERSONAL_BIRTHDAY'];
            $item['PERSONAL_BIRTHDAY'] = $birth ? $birth->toString() : '';
            $item['PERSONAL_BIRTHDAY_DATE'] = $birth ? $birth->format('Y-m-d') : '';
            $item['_PERSONAL_PHONE_NORMALIZED_'] = $this->cleanPhoneNumberValue($item['PERSONAL_PHONE'] ?? '');
            $item['_FULL_NAME_'] = trim($item['LAST_NAME'].' '.$item['NAME'].' '.$item['SECOND_NAME']);
            $usersList[] = $item;
        }

        // Старый вариант с использованием штатного API
        /*
        $itemsIterator = \CUser::GetList(
            $by = [
                'FULL_NAME' => 'asc',
                'UF_DISCOUNT_CARD' => 'asc',
                'ID' => 'asc',
            ],
            $order = null,
            $filter,
            [
                'SELECT' => [
                    'UF_DISCOUNT_CARD',
                ],
                'FIELDS' => [
                    'ID', 'NAME', 'LAST_NAME', 'SECOND_NAME',
                    'EMAIL', 'LOGIN',
                    'PERSONAL_PHONE', 'PERSONAL_BIRTHDAY',
                ]
            ]
        );
        while ($item = $itemsIterator->fetch()) {
            $item['_PERSONAL_PHONE_NORMALIZED_'] = $this->cleanPhoneNumberValue($item['PERSONAL_PHONE'] ?? '');
            $item['_FULL_NAME_'] = trim($item['LAST_NAME'].' '.$item['NAME'].' '.$item['SECOND_NAME']);
            $usersList[] = $item;
        }
        //*/

        return $usersList;
    }
}
