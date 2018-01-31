<?php

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use FourPaws\App\Application;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\External\SmsService;
use FourPaws\UserBundle\Service\UserService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\UserBundle\Entity\User;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use FourPaws\AppBundle\Serialization\ArrayOrFalseHandler;
use FourPaws\AppBundle\Serialization\BitrixBooleanHandler;
use FourPaws\AppBundle\Serialization\BitrixDateHandler;
use FourPaws\AppBundle\Serialization\BitrixDateTimeHandler;

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

    private $action = '';
    /** @var ManzanaService $manzanaService */
    private $manzanaService;
    /** @var UserService $userCurrentUserService */
    private $userCurrentUserService;
    /** @var Serializer $serializer */
    protected $serializer;
    /** @var \Bitrix\Main\DB\Connection $connection */
    protected $connection;
    /** @var bool $isTransactionStarted */
    private $isTransactionStarted;

    public function __construct($component = null)
    {
        // LazyLoggerAwareTrait не умеет присваивать имя по классам без неймспейса
        // делаем это вручную
        $this->logName = __CLASS__;

        parent::__construct($component);
        $this->connection = \Bitrix\Main\Application::getConnection();
    }

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

        $params['CACHE_TYPE'] = isset($params['CACHE_TYPE']) ? $params['CACHE_TYPE'] : 'A';
        $params['CACHE_TIME'] = isset($params['CACHE_TIME']) ? $params['CACHE_TIME'] : 3600;

        $params['SEND_USER_REGISTRATION_SMS'] = isset($params['SEND_USER_REGISTRATION_SMS']) && $params['SEND_USER_REGISTRATION_SMS'] === 'N' ? 'N' : 'Y';
        $params['REGISTRATION_SMS_TEXT'] = $params['REGISTRATION_SMS_TEXT'] ?? '';
        if (!$params['REGISTRATION_SMS_TEXT']) {
            $params['REGISTRATION_SMS_TEXT'] = 'Спасибо за регистрацию на сайте 4lapy.ru! 
            Теперь Вам доступны все возможности личного кабинета! Номер вашего телефона является логином, пароль для доступа #PASSWORD#. 
            Для авторизации перейдите по ссылке http://4lapy.ru/personal/.';
        }

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
            if ($this->request->get('action') === 'postForm') {
                if ($this->request->get('formName') === 'cardRegistration') {
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

    public function getUserService()
    {
        if (!$this->userCurrentUserService) {
            $this->userCurrentUserService = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
        }
        return $this->userCurrentUserService;
    }

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
     */
    public function getSerializer()
    {
        if (!$this->serializer) {
            $this->serializer = SerializerBuilder::create()->configureHandlers(
                function (HandlerRegistry $registry) {
                    $registry->registerSubscribingHandler(new BitrixDateHandler());
                    $registry->registerSubscribingHandler(new BitrixDateTimeHandler());
                    $registry->registerSubscribingHandler(new BitrixBooleanHandler());
                    $registry->registerSubscribingHandler(new ArrayOrFalseHandler());
                }
            )->build();
        }
        return $this->serializer;
    }


    public function cleanPhoneNumberValue($phone)
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
    public function getBitrixGenderByExternalGender($externalGenderCode)
    {
        $externalGenderCode = intval($externalGenderCode);
        if ($externalGenderCode === static::EXTERNAL_GENDER_CODE_M) {
            return static::BITRIX_GENDER_CODE_M;
        } elseif ($externalGenderCode === static::EXTERNAL_GENDER_CODE_F) {
            return static::BITRIX_GENDER_CODE_F;
        }

        return '';
    }

    protected function initialLoadAction()
    {
        $this->loadData();
    }

    protected function postFormAction()
    {
        $this->initPostFields();

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
        $fieldName = 'cardNumber';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value === '') {
            $this->setFieldError($fieldName, 'Undefined card number', 'empty');
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
                try {
                    /** @var FourPaws\External\Manzana\Model\CardValidateResult $validateResult */
                    $validateResult = $this->getManzanaService()->validateCardByNumberRaw($value);
                    // 0 - ok; 1 - карта не существует; 2 - карта принадлежит другому юзеру
                    $validationResultCode = intval($validateResult->validationResultCode);
                    if ($validationResultCode === 1) {
                        $this->setFieldError($fieldName, 'Not found', 'not_found');
                    } elseif ($validationResultCode === 2) {
                        /** @var FourPaws\External\Manzana\Model\Card $card */
                        $card = $this->getManzanaService()->searchCardByNumber($value);
                        if ($card) {
                            // Эта проверка была в старой реализации
// !!!
// Непонятно, какое отношение код семейного положения определяет активность карты...
// !!!
                            if ($card->familyStatusCode === 2) {
                                $this->setFieldError($fieldName, 'Card activated', 'activated');
                            }
                            // Эта проверка была в старой реализации
                            // Если HasChildrenCode=200000, анкета считается актуальной
// !!!
// Аналогично familyStatusCode, непонятно какая связь между кодом наличия детей и следующими флагами
// !!!
                            if ($card->hashChildrenCode === 200000) {
                                $this->arResult['CARD_DATA']['IS_ACTUAL_PROFILE'] = 'Y';
                            } else {
                                $this->arResult['CARD_DATA']['IS_ACTUAL_PROFILE'] = 'N';
                                //$this->arResult['CARD_DATA']['IS_ACTUAL_PHONE'] = 'N';
                                //$this->arResult['CARD_DATA']['IS_ACTUAL_EMAIL'] = 'N';
                            }
                            // товарищи из манзаны гарантируют: ненулевой pl_debet <=> карта бонусная
                            $this->arResult['CARD_DATA']['IS_BONUS_CARD'] = doubleval($card->plDebet) > 0 ? 'Y' : 'N';

                            $this->arResult['CARD_DATA']['USER'] = [
                                'CONTACT_ID' => htmlspecialcharsbx(trim($card->contactId)),
                                'LAST_NAME' => htmlspecialcharsbx(trim($card->lastName)),
                                'FIRST_NAME' => htmlspecialcharsbx(trim($card->firstName)),
                                'SECOND_NAME' => htmlspecialcharsbx(trim($card->secondName)),
                                'BIRTHDAY' => $card->birthDate ? $card->birthDate->format('d.m.Y') : '',
                                'PHONE' => $this->cleanPhoneNumberValue(trim($card->phone)),
                                'EMAIL' => htmlspecialcharsbx(trim($card->email)),
                                'GENDER_CODE' => intval($card->genderCode),
                            ];
                        }
                    }
                } catch (\Exception $exception) {
                    $this->setFieldError($fieldName, $exception->getMessage(), 'exception');

                    $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
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
                $this->setFieldError($fieldName, 'Undefined', 'empty');
            } else {
                if (strlen($value) < 3 || preg_match('/[^а-яА-ЯёЁ\-\s]/u', $value)) {
                    $this->setFieldError($fieldName, 'Not valid', 'not_valid');
                }
            }
        }

        $fieldName = 'birthDay';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value === '') {
            $this->setFieldError($fieldName, 'Undefined', 'empty');
        } else {
            if(!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value)) {
                $this->setFieldError($fieldName, 'Not valid', 'not_valid');
            } else {
                if (!$GLOBALS['DB']->IsDate($value, 'DD.MM.YYYY')) {
                    $this->setFieldError($fieldName, 'Not valid', 'not_valid');
                }
            }
        }

        $fieldName = 'genderCode';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value === '') {
            $this->setFieldError($fieldName, 'Undefined', 'empty');
        } else {
            if($value != static::EXTERNAL_GENDER_CODE_M && $value != static::EXTERNAL_GENDER_CODE_F) {
                $this->setFieldError($fieldName, 'Not valid', 'not_valid');
            }
        }
    }

    protected function processPhoneNumber()
    {
        $fieldName = 'phone';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value === '') {
            $this->setFieldError($fieldName, 'Undefined phone number', 'empty');
        } else {
            $phone = $this->cleanPhoneNumberValue($value);
            if ($phone !== '') {
                // Наличие юзера с таким номером в БД сайта
                // Проверка делалась в старой реализации, по текущему ТЗ она не требуется
                //if ($this->searchUserByPhoneNumber($phone)) {
                //    $this->setFieldError($fieldName, 'Already registered phone number', 'already_registered');
                //}
            } else {
                $this->setFieldError($fieldName, 'Not valid', 'not_valid');
            }
        }
    }

    protected function processEmail()
    {
        $fieldName = 'email';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value !== '') {
            if (!check_email($value)) {
                $this->setFieldError($fieldName, 'Not valid', 'not_valid');
            } else {
                if ($this->searchUserByEmail($value)) {
                    $this->setFieldError($fieldName, 'Already registered e-mail', 'already_registered');
                }
            }
        }
    }

    /**
     * @return Result
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
                    $this->startTransaction();
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
                        $this->rollbackTransaction();
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
                        $this->rollbackTransaction();
                        $result->addErrors($updateManzanaResult->getErrors());
                    }
                    $resultData['updateUserResults'][$userId]['manzana'] = $updateManzanaResult;
                }
                // внутри метода проверяется открыта ли транзакциия, поэтому его можно здесь вызывать
                $this->commitTransaction();
            }

            if ($createNewUser) {
                // Если пользователь не найден, Система создает новую учетную запись пользователя
                // с указанными личными данными.
                // Бонусная карта привязывается к профилю пользователя.
                $userSms = null;
                $userManzana = null;
                $this->startTransaction();
                $createResult = $this->createUserByFormFields();
                if ($createResult->isSuccess()) {
                    $createResultData = $createResult->getData();
                    // для sms нужно от createResult брать объект юзера, т.к. в нем хранится пароль
                    $userSms = clone $createResultData['user'];
                    // для Манзаны берем карточку из базы (нужны все поля)
                    $userManzana = $this->searchUserById($createResultData['user']->getId());
                    if (!$userManzana) {
                        $result->addError(
                            new Error('Не найден пользователь по id: '.$createResultData['user']->getId(), 'doCardRegistrationUserNotFound')
                        );
                        // не будем слать sms
                        $userSms = null;
                    }
                } else {
                    // откатываем транзакцию БД
                    $this->rollbackTransaction();
                    $result->addErrors($createResult->getErrors());
                }
                $resultData['createUserResults'][0]['result'] = $createResult;

                if ($userManzana) {
                    // отправка контактов юзера в Манзану
                    $updateManzanaResult = $this->doManzanaUpdateContact($userManzana);
                    if (!$updateManzanaResult->isSuccess()) {
                        // в Манзану данные не ушли - откатываемся
                        $this->rollbackTransaction();
                        $result->addErrors($updateManzanaResult->getErrors());
                        // не будем отправлять sms
                        $userSms = null;
                    }
                    $resultData['createUserResults'][0]['manzana'] = $updateManzanaResult;
                }
                $this->commitTransaction();

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
     */
    protected function convertUserToArray(User $user)
    {
        return $this->getSerializer()->toArray($user, SerializationContext::create()->setGroups(['update']));
    }

    /**
     * @param array $fields
     * @return User
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
            if (!$createResult) {
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

        $result->setData(
            [
                'userId' => $userId,
                'fields' => $fields,
            ]
        );

        return $result;
    }

    /**
     * @param User $user
     * @return Result
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

        if ($result->isSuccess()) {
            $manzanaService = $this->getManzanaService();
            // поиск контакта в Манзане по телефону (в старой реализации поиск делалася по номеру карты)
            try {
                $contactId = $manzanaService->getContactIdByPhone($phoneManzana);
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
                try {
                    $manzanaClient = new Client();
                    $this->getUserService()->setClientPersonalDataByCurUser($manzanaClient, $user);
                    if ($contactId !== '') {
                        $manzanaClient->contactId = $contactId;
                    }
                    // В старой реализации передавалось это поле с таким значением
// !!!
// Непонятно с какой целью устанавливается признак наличия детей
// !!!
                    $manzanaClient->hashChildrenCode = 200000;
                    // !!!
                    // Также в старой реализации передавались поля:
                    // ff_shopofactivation = UpdatedByСassa,
                    // ff_shopregistration = символьный код магазина (связь UF_SHOP),
                    // но в объекте Client этих полей нет
                    // !!!

                    $manzanaService->updateContact($manzanaClient);
                } catch (\Exception $exception) {
                    $result->addError(
                        new Error($exception->getMessage(), 'manzanaUpdateContactException')
                    );
                    $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
                }
            }
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
        $phone = $user->getNormalizePersonalPhone();
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

        if ($result->isSuccess()) {
            try {
                $smsService = new SmsService();
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
     */
    protected function getUserListByParams($params)
    {
        $filter = isset($params['filter']) ? $params['filter'] : [];

        $users = $this->getUserRepository()->findBy(
            $filter,
            (isset($params['order']) ? $params['order'] : []),
            (isset($params['limit']) ? $params['limit'] : null)
        );

        return $users;
    }

    /**
     * @param string $phone
     * @return User|null
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
