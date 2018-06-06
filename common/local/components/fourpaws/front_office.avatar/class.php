<?php

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Adv\Bitrixtools\Tools\Main\UserGroupUtils;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UserGroupTable;
use Bitrix\Main\UserTable;
use FourPaws\App\Application;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsFrontOfficeAvatarComponent extends \CBitrixComponent
{
    use LazyLoggerAwareTrait;

    /** код группы пользователей, имеющих доступ к компоненту, если ничего не задано в параметрах подключения */
    const DEFAULT_USER_GROUP_CODE = 'FRONT_OFFICE_USERS';
    const BX_ADMIN_GROUP_ID = 1;

    /** @var string $action */
    private $action = '';
    /** @var UserService $userCurrentUserService */
    private $userCurrentUserService;
    /** @var string $canAccess */
    protected $canAccess = '';
    /** @var array $userGroups */
    private $userGroups;
    /** @var bool $isUserAdmin */
    private $isUserAdmin;
    /** @var array $userOperations */
    private $userOperations;
    /** @var array $userSubordinateGroups */
    private $userSubordinateGroups;
    /** @var array $canAccessOperations */
    protected $canAccessOperations = [
        'view_subordinate_users',
        'view_all_users',
        'edit_subordinate_users',
    ];
    /** @var array $checkSubordinateOperations */
    protected $checkSubordinateOperations = [
        'view_all_users', 'edit_all_users'
    ];

    /**
     * FourPawsFrontOfficeAvatarComponent constructor.
     *
     * @param null|\CBitrixComponent $component
     */
    public function __construct($component = null)
    {
        // LazyLoggerAwareTrait не умеет присваивать имя по классам без неймспейса
        // делаем это вручную
        $this->logName = __CLASS__;

        parent::__construct($component);
    }

    /**
     * @param $params
     * @return mixed
     */
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
     * @return array
     */
    protected function getUserOperations()
    {
        if (!isset($this->userOperations)) {
            $userGroups = $this->getUserGroups();
            $this->userOperations = $userGroups ? array_keys($GLOBALS['USER']->GetAllOperations($userGroups)) : [];
        }

        return $this->userOperations;
    }

    /**
     * @param string $operationName
     * @return bool
     */
    protected function canUserDoOperation(string $operationName)
    {
        $result = false;
        if ($this->isUserAdmin()) {
            $result = true;
        }
        if (!$result) {
            $result = in_array($operationName, $this->getUserOperations());
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getUserSubordinateGroups()
    {
        if (!isset($this->userSubordinateGroups)) {
            $this->userSubordinateGroups = [];
            $userOperations = $this->getUserOperations();
            if (!in_array('edit_all_users', $userOperations) && !in_array('view_all_users', $userOperations)) {
                $userGroups = $this->getUserGroups();
                if ($userGroups) {
                    $this->userSubordinateGroups = \CGroup::GetSubordinateGroups($userGroups);
                }
            }
        }

        return $this->userSubordinateGroups;
    }

    /**
     * @return UserService
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getUserService()
    {
        if (!$this->userCurrentUserService) {
            $this->userCurrentUserService = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
        }

        return $this->userCurrentUserService;
    }

    /**
     * @return UserRepository
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getUserRepository()
    {
        return $this->getUserService()->getUserRepository();
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
                    // т.к. данный компонент связан с просмотром юзеров, то дополнительно проверяем операции уровней доступа
                    foreach ($this->canAccessOperations as $operationName) {
                        if ($this->canUserDoOperation($operationName)) {
                            $this->canAccess = 'Y';
                            break;
                        }
                    }
                }
            }
        }

        return $this->canAccess === 'Y';
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
        }

        return $action;
    }

    /**
     * doAction
     */
    protected function doAction()
    {
        $action = $this->getAction();
        if (is_callable(array($this, $action.'Action'))) {
            call_user_func(array($this, $action.'Action'));
        }
    }

    /**
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function initialLoadAction()
    {
        $this->loadData();
    }

    /**
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function postFormAction()
    {
        $this->initPostFields();

        if ($this->canAccess()) {
            $this->processSearchFormFields();
            $this->obtainUsersList();
        }

        $this->loadData();
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function userAuthAction()
    {
        $this->initPostFields();

        if ($this->canAccess()) {
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

        $this->loadData();
    }

    /**
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function loadData()
    {
        $this->arResult['IS_AUTHORIZED'] = $GLOBALS['USER']->isAuthorized() ? 'Y' : 'N';
        $this->arResult['CAN_ACCESS'] = $this->canAccess() ? 'Y' : 'N';
        $this->arResult['ACTION'] = $this->getAction();
        $this->arResult['IS_AVATAR_AUTHORIZED'] = $this->getUserService()->isAvatarAuthorized() ? 'Y' : 'N';

        $this->includeComponentTemplate();
    }

    /**
     * @param string $phone
     * @return string
     */
    public function cleanPhoneNumberValue(string $phone)
    {
        try {
            $phone = PhoneHelper::normalizePhone($phone);
        } catch (\Exception $exception) {
            $phone = '';
        }

        return $phone;
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
                if (!$this->canUserDoOperation($operation)) {
                    $filter['CHECK_SUBORDINATE'] = $this->getUserSubordinateGroups();
                    break;
                }
            }
            if (!$this->canUserDoOperation('edit_php')) {
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

    protected function initPostFields()
    {
        $this->arResult['~FIELD_VALUES'] = $this->request->getPostList()->toArray();
        $this->arResult['FIELD_VALUES'] = $this->walkRequestValues($this->arResult['~FIELD_VALUES']);
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

    /**
     * @param $value
     * @return array|mixed|string
     */
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
