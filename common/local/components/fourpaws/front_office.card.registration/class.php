<?php

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Adv\Bitrixtools\Tools\Main\UserGroupUtils;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\External\SmsService;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsFrontOfficeCardRegistrationComponent extends \CBitrixComponent
{
    use LazyLoggerAwareTrait;

    const EXTERNAL_GENDER_CODE_M = 1;
    const EXTERNAL_GENDER_CODE_F = 2;
    const BITRIX_GENDER_CODE_M = 'M';
    const BITRIX_GENDER_CODE_F = 'F';
    /** код группы пользователей, имеющих доступ к компоненту, если ничего не задано в параметрах подключения */
    const DEFAULT_USER_GROUP_CODE = 'FRONT_OFFICE_USERS';
    const BX_ADMIN_GROUP_ID = 1;

    /** @var string $action */
    private $action = '';
    /** @var ManzanaService $manzanaService */
    private $manzanaService;
    /** @var UserService $userCurrentUserService */
    private $userCurrentUserService;
    /** @var Serializer $serializer */
    protected $serializer;
    /** @var Connection $connection */
    protected $connection;
    /** @var bool $isTransactionStarted */
    private $isTransactionStarted = false;
    /** @var string $canAccess */
    protected $canAccess = '';
    /** @var array $userGroups */
    private $userGroups;
    /** @var bool $isUserAdmin */
    private $isUserAdmin;
    /** @var array $validateCardCache */
    private $validateCardCache = [];

    /**
     * FourPawsFrontOfficeCardRegistrationComponent constructor.
     *
     * @param null|\CBitrixComponent $component
     */
    public function __construct($component = null)
    {
        // LazyLoggerAwareTrait не умеет присваивать имя по классам без неймспейса
        // делаем это вручную
        $this->logName = __CLASS__;

        parent::__construct($component);
        $this->connection = \Bitrix\Main\Application::getConnection();
    }

    /**
     * @param array $params
     * @return array
     */
    public function onPrepareComponentParams($params)
    {
        $params['CURRENT_PAGE'] = isset($params['CURRENT_PAGE']) ? trim($params['CURRENT_PAGE']) : '';
        if (!$params['CURRENT_PAGE']) {
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

        $params['CACHE_TYPE'] = $params['CACHE_TYPE'] ?? 'A';
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 3600;

        $params['SEND_USER_REGISTRATION_SMS'] = isset($params['SEND_USER_REGISTRATION_SMS']) && $params['SEND_USER_REGISTRATION_SMS'] === 'N' ? 'N' : 'Y';
        $params['REGISTRATION_SMS_TEXT'] = $params['REGISTRATION_SMS_TEXT'] ?? '';
        if (!$params['REGISTRATION_SMS_TEXT']) {
            $params['REGISTRATION_SMS_TEXT'] = '';
            // без переносов строк
            $params['REGISTRATION_SMS_TEXT'] .= 'Спасибо за регистрацию на сайте 4lapy.ru!';
            $params['REGISTRATION_SMS_TEXT'] .= ' Теперь Вам доступны все возможности личного кабинета! Номер вашего телефона является логином, пароль для доступа #PASSWORD#.';
            $params['REGISTRATION_SMS_TEXT'] .= ' Для авторизации перейдите по ссылке https://4lapy.ru/personal/.';
        }

        $params['SHOP_OF_ACTIVATION'] = isset($params['SHOP_OF_ACTIVATION']) ? trim($params['SHOP_OF_ACTIVATION']) : 'UpdatedByСassa';

        $params = parent::onPrepareComponentParams($params);

        return $params;
    }

    /**
     * @throws Exception
     */
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

        if ($this->request->get('action') === 'postForm') {
            if ($this->request->get('formName') === 'cardRegistration') {
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
     * @throws ApplicationCreateException
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
     * @throws ApplicationCreateException
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
     * @throws ApplicationCreateException
     */
    public function getUserRepository()
    {
        return $this->getUserService()->getUserRepository();
    }

    protected function startTransaction()
    {
        if (!$this->isTransactionStarted) {
            $this->connection->startTransaction();
            $this->isTransactionStarted = true;
        }
    }

    protected function rollbackTransaction()
    {
        if ($this->isTransactionStarted) {
            $this->connection->rollbackTransaction();
            $this->isTransactionStarted = false;
        }
    }

    protected function commitTransaction()
    {
        if ($this->isTransactionStarted) {
            $this->connection->commitTransaction();
            $this->isTransactionStarted = false;
        }
    }

    /**
     * @return Serializer
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws SystemException
     */
    public function getSerializer(): Serializer
    {
        if (!$this->serializer) {
            try {
                $container = Application::getInstance()->getContainer();
            } catch (ApplicationCreateException $e) {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
                /** @noinspection PhpUnhandledExceptionInspection */
                throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
            }

            $this->serializer = $container->get(SerializerInterface::class);
        }

        return $this->serializer;
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
     * @return string
     */
    public function genPassword()
    {
        $password = randString(
            12,
            [
                'abcdefghijklnmopqrstuvwxyz',
                'ABCDEFGHIJKLNMOPQRSTUVWX­YZ',
                '0123456789',
                '<>/?;:[]{}|~!@#$%^&*()-_+=',
            ]
        );

        return $password;
    }

    /**
     * @param string $externalGenderCode
     * @return string
     */
    public function getBitrixGenderByExternalGender(string $externalGenderCode)
    {
        $result = '';
        $externalGenderCode = (int)$externalGenderCode;
        if ($externalGenderCode === static::EXTERNAL_GENDER_CODE_M) {
            $result = static::BITRIX_GENDER_CODE_M;
        } elseif ($externalGenderCode === static::EXTERNAL_GENDER_CODE_F) {
            $result = static::BITRIX_GENDER_CODE_F;
        }

        return $result;
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
            $this->processPersonalData();
            $this->processPhoneNumber();
            $this->processEmail();

            if (empty($this->arResult['ERROR']['FIELD'])) {
                if ($this->trimValue($this->getFormFieldValue('doCardRegistration')) === 'Y') {
                    $registrationResult = $this->doCardRegistration();

                    if ($registrationResult->isSuccess()) {
                        $this->arResult['REGISTRATION_STATUS'] = 'SUCCESS';
                    } else {
                        $this->arResult['REGISTRATION_STATUS'] = 'ERROR';
                        foreach ($registrationResult->getErrors() as $error) {
                            $this->arResult['ERROR']['REGISTRATION'][$error->getCode()] = $error->getMessage();
                        }
                    }
                }
            }
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
        $fieldName = 'cardNumber';
        $cardNumber = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($cardNumber === '') {
            $this->setFieldError($fieldName, 'Номер карты не задан', 'empty');
        } elseif (strlen($cardNumber) != 13) {
            $this->setFieldError($fieldName, 'Неверный номер карты', 'incorrect_value');
        } else {
            $continue = true;
            // Заказчик уточнил, что проверку надо делать в Манзане.
            /*
            if ($this->searchUserByCardNumber($value)) {
                $this->setFieldError($fieldName, 'Card activated', 'activated');
                $continue = false;
            }
            */
            if ($continue) {
                $validateResult = $this->validateCardByNumber($cardNumber);
                $validateResultData = $validateResult->getData();
                if (!$validateResult->isSuccess()) {
                    $this->setFieldError($fieldName, $validateResult->getErrors(), 'runtime');
                } elseif (empty($validateResultData['validate'])) {
                    $this->setFieldError($fieldName, 'Карта не найдена', 'not_found');
                }

                if ($validateResultData['validate']) {
                    // validationResultCode == 2
                    if ($validateResultData['validate']['_IS_CARD_OWNED_'] === 'Y') {
                        $searchCardResult = $this->searchCardByNumber($cardNumber);
                        $searchCardResultData = $searchCardResult->getData();
                        if (!$searchCardResult->isSuccess()) {
                            $this->setFieldError($fieldName, $searchCardResult->getErrors(), 'runtime');
                        } elseif (empty($searchCardResultData['card'])) {
                            $this->setFieldError($fieldName, 'Карта не найдена', 'not_found');
                        } else {
                            if ($searchCardResultData['card']['_IS_ACTIVATED_'] === 'Y') {
                                $this->setFieldError($fieldName, 'Карта уже активирована', 'activated');
                            } else {
                                $this->arResult['CARD_DATA']['IS_ACTUAL_CONTACT'] = $searchCardResultData['card']['_IS_ACTUAL_CONTACT_'];
                                $this->arResult['CARD_DATA']['IS_BONUS_CARD'] = $searchCardResultData['card']['_IS_BONUS_CARD_'];
                                $this->arResult['CARD_DATA']['USER'] = [
                                    'CONTACT_ID' => $searchCardResultData['card']['CONTACT_ID'],
                                    'LAST_NAME' => $searchCardResultData['card']['LAST_NAME'],
                                    'FIRST_NAME' => $searchCardResultData['card']['FIRST_NAME'],
                                    'SECOND_NAME' => $searchCardResultData['card']['SECOND_NAME'],
                                    'BIRTHDAY' => $searchCardResultData['card']['BIRTHDAY'],
                                    'PHONE' => $searchCardResultData['card']['PHONE'],
                                    'EMAIL' => $searchCardResultData['card']['EMAIL'],
                                    'GENDER_CODE' => $searchCardResultData['card']['GENDER_CODE'],
                                    '_PHONE_NORMALIZED_' => $this->cleanPhoneNumberValue($searchCardResultData['card']['PHONE']),
                                ];
                            }
                        }
                    } elseif ($validateResultData['validate']['_IS_CARD_NOT_EXISTS_'] === 'Y') {
                        $this->setFieldError($fieldName, 'Not found', 'not_found');
                    }
                }
            }
        }
    }

    protected function processPersonalData()
    {
        $tmpList = [
            'lastName',
            'firstName',
            'secondName',
        ];
        foreach ($tmpList as $fieldName) {
            $value = $this->trimValue($this->getFormFieldValue($fieldName));
            if ($value === '') {
                $this->setFieldError($fieldName, 'Значение не задано', 'empty');
            } else {
                if (strlen($value) < 3 || preg_match('/[^а-яА-ЯёЁ\-\s]/u', $value)) {
                    $this->setFieldError($fieldName, 'Значение задано некорректно', 'not_valid');
                }
            }
        }

        $fieldName = 'birthDay';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value === '') {
            $this->setFieldError($fieldName, 'Значение не задано', 'empty');
        } else {
            if(!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value)) {
                $this->setFieldError($fieldName, 'Значение задано некорректно', 'not_valid');
            } else {
                if (!$GLOBALS['DB']->IsDate($value, 'DD.MM.YYYY')) {
                    $this->setFieldError($fieldName, 'Значение задано некорректно', 'not_valid');
                }
            }
        }

        $fieldName = 'genderCode';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value === '') {
            $this->setFieldError($fieldName, 'Значение не задано', 'empty');
        } else {
            if($value != static::EXTERNAL_GENDER_CODE_M && $value != static::EXTERNAL_GENDER_CODE_F) {
                $this->setFieldError($fieldName, 'Значение задано некорректно', 'not_valid');
            }
        }
    }

    protected function processPhoneNumber()
    {
        $fieldName = 'phone';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value === '') {
            $this->setFieldError($fieldName, 'Не задан номер телефона', 'empty');
        } else {
            $phone = $this->cleanPhoneNumberValue($value);
            if ($phone !== '') {
                // Наличие юзера с таким номером в БД сайта
                // Проверка делалась в старой реализации, по текущему ТЗ она не требуется
                //if ($this->searchUserByPhoneNumber($phone)) {
                //    $this->setFieldError($fieldName, 'Already registered phone number', 'already_registered');
                //}
            } else {
                $this->setFieldError($fieldName, 'Номер телефона задан некорректно', 'not_valid');
            }
        }
    }

    protected function processEmail()
    {
        $fieldName = 'email';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value !== '') {
            if (!check_email($value)) {
                $this->setFieldError($fieldName, 'E-mail задан некорректно', 'not_valid');
            } else {
                if ($this->searchUserByEmail($value)) {
                    $this->setFieldError($fieldName, 'Пользователь с заданным e-mail уже зарегистрирован', 'already_registered');
                }
            }
        }
    }

    /**
     * @param string $cardNumber
     * @param bool $getFromCache
     * @return Result
     */
    public function validateCardByNumber(string $cardNumber, bool $getFromCache = false)
    {
        if ($getFromCache && isset($this->validateCardCache[$cardNumber])) {
            return $this->validateCardCache[$cardNumber];
        }

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
            // validationResultCode == 2
            $validate['_IS_CARD_OWNED_'] = $validateRaw->isCardOwned() ? 'Y' : 'N';
            $validate['_IS_CARD_NOT_EXISTS_'] = $validateRaw->isCardNotExists() ? 'Y' : 'N';
        }

        $result->setData(
            [
                'validate' => $validate,
                'validateRaw' => $validateRaw,
            ]
        );

        $this->validateCardCache[$cardNumber] = $result;

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
            $card['GENDER_CODE'] = (int)$cardRaw->genderCode;
            $card['FAMILY_STATUS_CODE'] = (int)$cardRaw->familyStatusCode;
            $card['HAS_CHILDREN_CODE'] = (int)$cardRaw->hashChildrenCode;
            $card['DEBET'] = (double)$cardRaw->plDebet;
            $card['_IS_BONUS_CARD_'] = $cardRaw->isBonusCard() ? 'Y' : 'N';
            $card['_IS_ACTUAL_CONTACT_'] = $cardRaw->isActualContact() ? 'Y' : 'N';
            $card['_IS_LOAYALTY_PROGRAM_CONTACT_'] = $cardRaw->isLoyaltyProgramContact() ? 'Y' : 'N';
            $card['_IS_ACTIVATED_'] = $card['_IS_ACTUAL_CONTACT_'] === 'Y' && $card['_IS_LOAYALTY_PROGRAM_CONTACT_'] ? 'Y' : 'N';
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
     * @return Result
     * @throws ApplicationCreateException
     */
    protected function doCardRegistration()
    {
        $result = new Result();

        $phone = $this->cleanPhoneNumberValue($this->getFormFieldValue('phone'));
        if ($phone === '') {
            $result->addError(
                new Error('Не задан номер телефона', 'emptyPhoneField')
            );
        }

        $cardNumber = $this->trimValue($this->getFormFieldValue('cardNumber'));
        if ($cardNumber === '') {
            $result->addError(
                new Error('Не задан номер карты', 'emptyCardNumberField')
            );
        }

        $resultData = [];
        if ($result->isSuccess()) {
            $users = $this->getUserListByParams(
                [
                    'filter' => [
                        'ACTIVE' => 'Y',
                        [
                            'LOGIC' => 'OR',
                            [
                                '=PERSONAL_PHONE' => $phone
                            ],
                            [
                                '=UF_DISCOUNT_CARD' => $cardNumber
                            ],
                        ]
                    ]
                ]
            );

            $createNewUser = true;
            foreach ($users as $user) {
                /** @var User $user */
                if ($user->isFastOrderUser()) {
                    continue;
                }
                $createNewUser = false;
                $cardNumberUser = trim($user->getDiscountCardNumber());
                $phoneUser = $this->cleanPhoneNumberValue($user->getPersonalPhone());
                $userId = $user->getId();
                $updateUser = false;
                if ($phoneUser == $phone) {
                    /*
                    // Если найден пользователь с указанным телефоном без привязанной бонусной карты,
                    // бонусная карта привязывается к профилю пользователя.
                    // Если найден пользователь с указанным телефоном и другим номером бонусной карты,
                    // данные о номере бонусной карты обновляются в профиле пользователя.
                    if ($cardNumberUser === '' || $cardNumberUser != $cardNumber) {
                        $updateUser = true;
                    }
                    */
                    // 25.01.2018: обсудили с Марией Цегельник в чате и решили делать апдейт профиля по полям всей формы
                    $updateUser = true;
                } elseif ($cardNumberUser == $cardNumber) {
                    /*
                    // Если найден пользователь с указанным номером бонусной карты без номера телефона или
                    // с другим номером телефона, данные о номере телефона обновляются в профиле пользователя.
                    // Бонусная карта привязывается к профилю пользователя
                    if ($phoneUser === '' || $phoneUser != $phone) {
                        $updateUser = true;
                    }
                    */
                    // 25.01.2018: обсудили с Марией Цегельник в чате и решили делать апдейт профиля по полям всей формы
                    $updateUser = true;
                }

                $userManzana = clone $user;
                if ($updateUser) {
                    /**
                     * С транзакциями вылетает ошибка "MySQL server has gone away", вероятно, из-за долгих запросов к ML.
                     * Отключил нафиг.
                     **/
                    //$this->startTransaction();
                    $updateResult = $this->updateUserByFormFields($userId);
                    if ($updateResult->isSuccess()) {
                        // для отправки в Манзану берем обновленную карточку из базы
                        $userManzana = $this->searchUserById($userId);
                        if (!$userManzana) {
                            $result->addError(
                                new Error('Не найден пользователь по id: '.$userId, 'doCardRegistrationUserNotFound')
                            );
                        }
                    } else {
                        // откатываем транзакцию БД
                        //$this->rollbackTransaction();
                        $result->addErrors($updateResult->getErrors());
                        $userManzana = null;
                    }
                    $resultData['updateUserResults'][$userId]['result'] = $updateResult;
                }
                if ($userManzana) {
                    // отправка контактов юзера в Манзану
                    $updateManzanaResult = $this->doManzanaUpdateContact($userManzana);
                    if (!$updateManzanaResult->isSuccess()) {
                        // в Манзану данные не ушли - откатываемся
                        //$this->rollbackTransaction();
                        $result->addErrors($updateManzanaResult->getErrors());
                    }
                    $resultData['updateUserResults'][$userId]['manzana'] = $updateManzanaResult;
                }
                // внутри метода проверяется открыта ли транзакциия, поэтому его можно здесь вызывать
                //$this->commitTransaction();
            }

            if ($createNewUser) {
                // Если пользователь не найден, Система создает новую учетную запись пользователя
                // с указанными личными данными.
                // Бонусная карта привязывается к профилю пользователя.
                $userSms = null;
                $userManzana = null;
                //$this->startTransaction();
                $createResult = $this->createUserByFormFields();
                if ($createResult->isSuccess()) {
                    $createResultData = $createResult->getData();
                    // для sms нужно от createResult брать объект юзера, т.к. в нем хранится пароль
                    $userSms = clone $createResultData['user'];
                    // для Манзаны берем карточку из базы (нужны все поля)
                    /** @var User $createdUser */
                    $createdUser = $createResultData['user'];
                    $userManzana = $this->searchUserById($createdUser->getId());
                    if (!$userManzana) {
                        $result->addError(
                            new Error('Не найден пользователь по id: '.$createdUser->getId(), 'doCardRegistrationUserNotFound')
                        );
                        // не будем слать sms
                        $userSms = null;
                    }
                } else {
                    // откатываем транзакцию БД
                    //$this->rollbackTransaction();
                    $result->addErrors($createResult->getErrors());
                }
                $resultData['createUserResults'][0]['result'] = $createResult;

                if ($userManzana) {
                    // отправка контактов юзера в Манзану
                    $updateManzanaResult = $this->doManzanaUpdateContact($userManzana);
                    if (!$updateManzanaResult->isSuccess()) {
                        // в Манзану данные не ушли - откатываемся
                        //$this->rollbackTransaction();
                        $result->addErrors($updateManzanaResult->getErrors());
                        // не будем отправлять sms
                        $userSms = null;
                    }
                    $resultData['createUserResults'][0]['manzana'] = $updateManzanaResult;
                }
                //$this->commitTransaction();

                if ($userSms) {
                    // отправка юзеру sms о регистрации на сайте
                    if ($this->arParams['SEND_USER_REGISTRATION_SMS'] === 'Y') {
                        $resultData['createUserResults'][0]['sms'] = $this->sendUserRegistrationSms($userSms);
                    }
                }
            }
        }

        $result->setData($resultData);

        return $result;
    }

    /**
     * @return array
     */
    protected function userArrayByFormFields()
    {
        $fields = [];
        $fields['NAME'] = $this->trimValue(
            $this->getFormFieldValue('firstName')
        );
        $fields['SECOND_NAME'] = $this->trimValue(
            $this->getFormFieldValue('secondName')
        );
        $fields['LAST_NAME'] = $this->trimValue(
            $this->getFormFieldValue('lastName')
        );
        $fields['PERSONAL_GENDER'] = $this->getBitrixGenderByExternalGender(
            $this->getFormFieldValue('genderCode')
        );
        $fields['PERSONAL_PHONE'] = $this->cleanPhoneNumberValue(
            $this->getFormFieldValue('phone')
        );
        $fields['UF_DISCOUNT_CARD'] = $this->trimValue(
            $this->getFormFieldValue('cardNumber')
        );

        $value = $this->trimValue(
            $this->getFormFieldValue('email')
        );
        if ($value !== '') {
            $fields['EMAIL'] = $value;
        }

        $value = $this->trimValue(
            $this->getFormFieldValue('birthDay')
        );
        if ($value !== '') {
            try {
                $fields['PERSONAL_BIRTHDAY'] = (new \Bitrix\Main\Type\Date($value, 'd.m.Y'));
            } catch (\Exception $exception) {}
        }

        return $fields;
    }

    /**
     * @return User
     */
    protected function userByFormFields()
    {
        /*
        $user = $this->convertUserFromArray(
            $this->userArrayByFormFields()
        );
        */

        $user = new User();
        $user->setName(
            $this->trimValue(
                $this->getFormFieldValue('firstName')
            )
        );
        $user->setSecondName(
            $this->trimValue(
                $this->getFormFieldValue('secondName')
            )
        );
        $user->setLastName(
            $this->trimValue(
                $this->getFormFieldValue('lastName')
            )
        );
        $user->setGender(
            $this->getBitrixGenderByExternalGender(
                $this->getFormFieldValue('genderCode')
            )
        );
        $user->setPersonalPhone(
            $this->cleanPhoneNumberValue(
                $this->getFormFieldValue('phone')
            )
        );
        $user->setDiscountCardNumber(
            $this->trimValue(
                $this->getFormFieldValue('cardNumber')
            )
        );

        $value = $this->trimValue(
            $this->getFormFieldValue('email')
        );
        if ($value !== '') {
            $user->setEmail($value);
        }

        $value = $this->trimValue(
            $this->getFormFieldValue('birthDay')
        );
        if ($value !== '') {
            try {
                $user->setBirthday(
                    (new \Bitrix\Main\Type\Date($value, 'd.m.Y'))
                );
            } catch (\Exception $exception) {}
        }

        return $user;
    }

    /**
     * @param User $user
     * @return array
     * @throws SystemException
     */
    protected function convertUserToArray(User $user)
    {
        return $this->getSerializer()->toArray($user, SerializationContext::create()->setGroups(['update']));
    }

    /**
     * @param array $fields
     * @return User
     * @throws SystemException
     */
    protected function convertUserFromArray(array $fields)
    {
        return $this->getSerializer()->fromArray($fields, DeserializationContext::create()->setGroups(['update']));
    }

    /**
     * @return Result
     */
    protected function createUserByFormFields()
    {
        $user = $this->userByFormFields();

        $user->setActive('Y');
        $user->setLogin($user->getPersonalPhone());
        $user->setPassword($this->genPassword());

        return $this->createUser($user);
    }

    /**
     * @param User $user
     * @return Result
     */
    protected function createUser(User $user)
    {
        $result = new Result();

        try {
            $createResult = $this->getUserRepository()->create($user);
            if ($createResult) {
                if ($user->getId()) {
                    // привязка пользователя к группе "Зарегистрированные пользователи"
                    $registeredUserGroupId = \CGroup::GetIDByCode('REGISTERED_USERS');
                    if ($registeredUserGroupId) {
                        \CUser::AppendUserGroup($user->getId(), [$registeredUserGroupId]);
                    }
                }
            } else {
                $result->addError(
                    new Error('Нераспознанная ошибка', 'createUserUnknownError')
                );
            }
        } catch (\Exception $exception) {
            $result->addError(
                new Error($exception->getMessage(), 'createUserException')
            );

            $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
        }

        $result->setData(
            [
                'user' => $user,
            ]
        );

        return $result;
    }

    /**
     * @param int $userId
     * @return Result
     */
    protected function updateUserByFormFields(int $userId)
    {
        $fields = $this->userArrayByFormFields();

        return $this->updateUser($userId, $fields);
    }

    /**
     * @param int $userId
     * @param array $fields
     * @return Result
     */
    protected function updateUser(int $userId, array $fields)
    {
        $result = new Result();

        if ($userId <= 0) {
            $result->addError(
                new Error('Не задан id пользователя, либо задан некорректно', 'updateUserIncorrectUserId')
            );
        }
        if ($result->isSuccess()) {
            if (isset($fields['ID'])) {
                unset($fields['ID']);
            }

            try {
                $updateResult = $this->getUserRepository()->updateData($userId, $fields);
                if (!$updateResult) {
                    $result->addError(new Error('Нераспознанная ошибка', 'updateUserUnknownError'));
                }
            } catch (\Exception $exception) {
                $result->addError(new Error($exception->getMessage(), 'updateUserException'));

                $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
            }
        }

        // сброс тегированного кеша, относящегося к юзеру, используемого в компонентах сайта
        $clearTags = [];
        $clearTags[] = 'user:'.$userId;
        if ($clearTags) {
            TaggedCacheHelper::clearManagedCache($clearTags);
        }

        $result->setData(
            [
                'userId' => $userId,
                'fields' => $fields,
            ]
        );

        return $result;
    }

    /**
     * @return string
     */
    protected function getShopOfActivation()
    {
        return $this->arParams['SHOP_OF_ACTIVATION'];
    }

    /**
     * @return string
     * @throws ApplicationCreateException
     */
    protected function getShopRegistration()
    {
        $currentUser = $this->searchUserById($this->arParams['USER_ID']);

        return $currentUser ? $currentUser->getShopCode() : '';
    }


    /**
     * Следует ли устанавливать флаг актуальности контакта
     *
     * @return bool
     */
    private function shouldSetActualContact()
    {
        // Если карта регистрируется через
        // ЛК магазина (касса) или ЛК магазина (планшет),
        // то автоматически устанавливаем флаг актуальности контакта.
        // Возможно, в будущем по каким-то другим условиям нужно будет определять
        return in_array($this->getShopOfActivation(), ['UpdatedByTab', 'UpdatedByСassa']);
    }

    /**
     * @param User $user
     * @return Result
     * @throws ApplicationCreateException
     */
    protected function doManzanaUpdateContact(User $user)
    {
        $result = new Result();

        $phoneManzana = $user->getManzanaNormalizePersonalPhone();
        if ($phoneManzana === '') {
            $result->addError(
                new Error('Не задан телефон для отправки данных в Manzana Loyalty', 'manzanaUpdateContactEmptyPhone')
            );
        }

        $manzanaClient = null;
        $contactId = '';
        $userId = $user->getId();

        if ($result->isSuccess()) {
            $manzanaService = $this->getManzanaService();
            // поиск контакта в Манзане по телефону (в старой реализации поиск делалася по номеру карты)
            $contact = null;
            try {
                if (!empty($phoneManzana)) {
                    $contact = $manzanaService->getContactByPhone($phoneManzana);
                    $contactId = $contact->contactId;
                }
            } catch (ManzanaServiceContactSearchNullException $exception) {
                // контакта с заданным номером телефона в Манзане нет - будет создан
                $this->log()->debug(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'manzanaContactIdByPhoneException')
                );
                $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
            }

            if ($result->isSuccess()) {
                $cardNumber = $user->getDiscountCardNumber();
                $logContext = [
                    'userId' => $userId,
                    'phoneManzana' => $phoneManzana,
                    'contactId' => $contactId,
                    'cardNumber' => $cardNumber,
                ];

                try {
                    $manzanaClient = new Client();
                    $this->getUserService()->setClientPersonalDataByCurUser($manzanaClient, $user);
                    if ($contactId !== '') {
                        $manzanaClient->contactId = $contactId;
                    }

                    $manzanaClient->cardnumber = $cardNumber;

                    // Код места активации карты
                    $val = $this->getShopOfActivation();
                    if ($val !== '') {
                        $manzanaClient->shopOfActivation = $val;
                    }
                    // Код места регистрации карты (от юзера, заданного в праметрах компонента определяется)
                    $val = $this->getShopRegistration();
                    if ($val !== '') {
                        $manzanaClient->shopRegistration = $val;
                    }
                    // автоматическая установка флага актуальности контакта
                    if ($this->shouldSetActualContact()) {
                        $manzanaClient->setActualContact(true);
                    }

                    // ML: установка введенной карты в качестве активной карты контакта
                    if ($cardNumber) {
                        $currentActiveCardId = '';
                        /** @var \Doctrine\Common\Collections\ArrayCollection $contactCards */
                        $contactCards = $contact->cards;
                        if ($contactCards && is_object($contactCards) && !$contactCards->isEmpty()) {
                            foreach ($contactCards as $tmpContactCard) {
                                $tmpCard = $manzanaService->getCardInfo($tmpContactCard->cardNumber, $contactId);
                                if ($tmpCard && $tmpCard->isActive()) {
                                    $currentActiveCardId = $tmpCard->cardId;
                                    break;
                                }
                            }
                        }

                        $this->log()->debug(
                            sprintf(
                                '%s currentActiveCardId: %s',
                                __FUNCTION__,
                                $currentActiveCardId
                            ),
                            $logContext
                        );

                        if ($currentActiveCardId) {
                            $validateResultData = $this->validateCardByNumber($cardNumber, true)->getData();
                            if (isset($validateResultData['validate']['CARD_ID'])) {
                                $newActivateCardId = $validateResultData['validate']['CARD_ID'];
                                $this->log()->debug(
                                    sprintf(
                                        '%s newActivateCardId: %s',
                                        __FUNCTION__,
                                        $newActivateCardId
                                    ),
                                    $logContext
                                );

                                if ($currentActiveCardId !== $newActivateCardId) {
                                    $tmpRes = $manzanaService->changeCard($currentActiveCardId, $newActivateCardId);
                                    $this->log()->debug(
                                        sprintf(
                                            '%s changeCard: %s',
                                            __FUNCTION__,
                                            $tmpRes ? 'success' : 'fail'
                                        ),
                                        $logContext
                                    );
                                    if (!$tmpRes) {
                                        throw new ContactUpdateException('Не удалось привязать карту');
                                    }
                                }
                            }
                        }
                    }

                    $manzanaService->updateContact($manzanaClient);

                    $this->log()->debug(
                        sprintf(
                            '%s updateContact: %s',
                            __FUNCTION__,
                            'success'
                        ),
                        $logContext
                    );

                } catch (\Exception $exception) {
                    $result->addError(
                        new Error($exception->getMessage(), 'manzanaUpdateContactException')
                    );
                    $this->log()->error(
                        sprintf(
                            '%s exception: %s',
                            __FUNCTION__,
                            $exception->getMessage()
                        ),
                        $logContext
                    );
                }
            }
        }

        // сброс тегированного кеша, относящегося к юзеру, используемого в компонентах сайта
        $clearTags = [];
        $clearTags[] = 'personal:bonus:'.$userId;
        if ($clearTags) {
            TaggedCacheHelper::clearManagedCache($clearTags);
        }

        $result->setData(
            [
                'user' => $user,
                'contactId' => $contactId,
                'manzanaClient' => $manzanaClient
            ]
        );

        return $result;
    }

    /**
     * @param User $user
     * @return Result
     */
    protected function sendUserRegistrationSms(User $user)
    {
        $phone = trim($user->getNormalizePersonalPhone());
        $password = $user->getPassword();
        $login = $user->getLogin();
        $text = str_replace(
            ['#LOGIN#', '#PASSWORD#', '#PHONE#'],
            [$login, $password, $phone],
            $this->arParams['REGISTRATION_SMS_TEXT']
        );

        return $this->sendSms($phone, $text);
    }

    /**
     * @param string $phone
     * @param string $text
     * @return Result
     */
    protected function sendSms($phone, $text)
    {
        $result = new Result();

        if ($phone === '') {
            $result->addError(
                new Error('Не задан телефон для отправки SMS', 'sendSmsEmptyPhone')
            );
        }
        if ($text === '') {
            $result->addError(
                new Error('Не задано сообщение SMS', 'sendSmsEmptyText')
            );
        }

        $this->log()->info(
            __FUNCTION__,
            [
                'phone' => $phone,
                'resultSuccess' => $result->isSuccess()
            ]
        );

        if ($result->isSuccess()) {
            try {
                /** @var SmsService $smsService */
                $smsService = Application::getInstance()->getContainer()->get('sms.service');
                $smsService->sendSmsImmediate($text, $phone);
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'sendSmsException')
                );

                $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
            }
        }

        $result->setData(
            [
                'phone' => $phone,
                'text' => $text,
            ]
        );

        return $result;
    }

    /**
     * @param array $params
     * @return array
     * @throws ApplicationCreateException
     */
    protected function getUserListByParams($params)
    {
        $filter = $params['filter'] ?? [];

        $users = $this->getUserRepository()->findBy(
            $filter,
            ($params['order'] ?? []),
            ($params['limit'] ?? null)
        );

        return $users;
    }

    /**
     * @param string $phone
     * @return User|null
     * @throws ApplicationCreateException
     */
    protected function searchUserByPhoneNumber(string $phone)
    {
        $user = null;
        $phone = trim($phone);
        if ($phone !== '') {
            // ищем пользователя с таким телефоном в БД сайта
            $items = $this->getUserListByParams(
                [
                    'filter' => [
                        '=ACTIVE' => 'Y',
                        '=PERSONAL_PHONE' => $phone,
                    ]
                ]
            );
            foreach ($items as $item) {
                if (!$item->isFastOrderUser()) {
                    $user = $item;
                    break;
                }
            }
        }

        $this->log()->debug(
            sprintf('Method: %s', __FUNCTION__),
            [
                'args' => func_get_args(),
                'return' => $user,
            ]
        );

        return $user;
    }

    /**
     * @param string $email
     * @return User|null
     * @throws ApplicationCreateException
     */
    protected function searchUserByEmail(string $email)
    {
        $user = null;
        $email = trim($email);
        if ($email !== '') {
            $items = $this->getUserListByParams(
                [
                    'filter' => [
                        '=ACTIVE' => 'Y',
                        '=EMAIL' => $email,
                    ],
                    'limit' => 1
                ]
            );
            $user = reset($items);
        }

        $this->log()->debug(
            sprintf('Method: %s', __FUNCTION__),
            [
                'args' => func_get_args(),
                'return' => $user,
            ]
        );

        return $user;
    }

    /**
     * @param string $cardNumber
     * @return User|null
     * @throws ApplicationCreateException
     */
    protected function searchUserByCardNumber(string $cardNumber)
    {
        $user = null;
        $cardNumber = trim($cardNumber);
        if ($cardNumber !== '') {
            $items = $this->getUserListByParams(
                [
                    'filter' => [
                        '=ACTIVE' => 'Y',
                        '=UF_DISCOUNT_CARD' => $cardNumber,
                    ],
                    'limit' => 1
                ]
            );
            $user = reset($items);
        }

        $this->log()->debug(
            sprintf('Method: %s', __FUNCTION__),
            [
                'args' => func_get_args(),
                'return' => $user,
            ]
        );

        return $user;
    }

    /**
     * @param int $userId
     * @return User|null
     * @throws ApplicationCreateException
     */
    protected function searchUserById(int $userId)
    {
        $user = null;
        if ($userId > 0) {
            $items = $this->getUserListByParams(
                [
                    'filter' => [
                        '=ACTIVE' => 'Y',
                        '=ID' => $userId,
                    ]
                ]
            );
            $user = reset($items);
        }

        $this->log()->debug(
            sprintf('Method: %s', __FUNCTION__),
            [
                'args' => func_get_args(),
                'return' => $user,
            ]
        );

        return $user;
    }

    /**
     * @param $fieldName
     * @param bool $getSafeValue
     * @return string|null
     */
    protected function getFormFieldValue($fieldName, $getSafeValue = false)
    {
        $key = $getSafeValue ? 'FIELD_VALUES' : '~FIELD_VALUES';

        return $this->arResult[$key][$fieldName] ?? null;
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
