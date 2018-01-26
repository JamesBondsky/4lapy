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

    public function __construct($component = null)
    {
        parent::__construct($component);
        $this->connection = \Bitrix\Main\Application::getConnection();
    }

    public function onPrepareComponentParams($params)
    {
        $params['CACHE_TYPE'] = isset($params['CACHE_TYPE']) ? $params['CACHE_TYPE'] : 'A';
        $params['CACHE_TIME'] = isset($params['CACHE_TIME']) ? $params['CACHE_TIME'] : 3600;

        $params['SEND_USER_REGISTRATION_SMS'] = isset($params['SEND_USER_REGISTRATION_SMS']) && $params['SEND_USER_REGISTRATION_SMS'] === 'N' ? 'N' : 'Y';
        $params['REGISTRATION_SMS_TEXT'] = $params['REGISTRATION_SMS_TEXT'] ?? '';
        if (!strlen($params['REGISTRATION_SMS_TEXT'])) {
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
        $action = 'initialLoad';
        if ($this->request->get('action') === 'postForm')  {
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
        if (!strlen($value)) {
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
                            // эта проверка была в старой реализации
                            if ($card->familyStatusCode === 2) {
                                $this->setFieldError($fieldName, 'Card activated', 'activated');
                            }

                            // если HasChildrenCode=200000, анкета считается актуальной
                            if ($card->hashChildrenCode === 200000) {
                                $this->arResult['CARD_DATA']['IS_ACTUAL_PROFILE'] = 'Y';
                            } else {
                                $this->arResult['CARD_DATA']['IS_ACTUAL_PROFILE'] = 'N';
                                $this->arResult['CARD_DATA']['IS_ACTUAL_PHONE'] = 'N';
                                $this->arResult['CARD_DATA']['IS_ACTUAL_EMAIL'] = 'N';
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
            if (!strlen($value)) {
                $this->setFieldError($fieldName, 'Undefined', 'empty');
            } else {
                if (strlen($value) < 3 || preg_match('/[^а-яА-ЯёЁ\-\s]/u', $value)) {
                    $this->setFieldError($fieldName, 'Not valid', 'not_valid');
                }
            }
        }

        $fieldName = 'birthDay';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if (!strlen($value)) {
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
        if (!strlen($value)) {
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
        if (!strlen($value)) {
            $this->setFieldError($fieldName, 'Undefined phone number', 'empty');
        } else {
            $phone = $this->cleanPhoneNumberValue($value);
            if (strlen($phone)) {
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
        if (strlen($value)) {
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
        if (!strlen($phone)) {
            $result->addError(
                new Error('Не задан номер телефона', 'emptyPhoneField')
            );
        }

        $cardNumber = $this->trimValue($this->getFormFieldValue('cardNumber'));
        if (!strlen($cardNumber)) {
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
                $updateUser = false;
                if ($phoneUser == $phone) {
                    // Если найден пользователь с указанным телефоном без привязанной бонусной карты,
                    // бонусная карта привязывается к профилю пользователя.
                    // Если найден пользователь с указанным телефоном и другим номером бонусной карты,
                    // данные о номере бонусной карты обновляются в профиле пользователя.
                    if (!strlen($cardNumberUser) || $cardNumberUser != $cardNumber) {
                        $updateUser = true;
                    }
                } elseif ($cardNumberUser == $cardNumber) {
                    // Если найден пользователь с указанным номером бонусной карты без номера телефона или
                    // с другим номером телефона, данные о номере телефона обновляются в профиле пользователя.
                    // Бонусная карта привязывается к профилю пользователя
                    if (!strlen($phoneUser) || $phoneUser != $phone) {
                        $updateUser = true;
                    }
                }
                if ($updateUser) {
                    $updateResult = $this->updateUserByFormFields($user->getId());
                    if (!$updateResult->isSuccess()) {
                        $result->addErrors($updateResult->getErrors());
                    }
                    $resultData['updateResults'][] = $updateResult;
                }
            }

            if ($createNewUser) {
                // Если пользователь не найден, Система создает новую учетную запись пользователя
                // с указанными личными данными.
                // Бонусная карта привязывается к профилю пользователя.
                $createResult = $this->createUserByFormFields();
                if (!$createResult->isSuccess()) {
                    $result->addErrors($createResult->getErrors());
                }
                $resultData['createResults'][] = $createResult;
            }
        }

        $result->setData($resultData);

        return $result;
    }

    /**
     * @return User
     */
    protected function userByFormFields()
    {
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
        if (strlen($value)) {
            $user->setEmail($value);
        }

        $value = $this->trimValue(
            $this->getFormFieldValue('birthDay')
        );
        if (strlen($value)) {
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

        // открываем транзакцию (на случай, если с Манзаной что-то пойдет не так)
        $this->connection->startTransaction();

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

        $updateManzanaResult = null;
        $smsResult = null;
        if ($result->isSuccess()) {
            // отправка контактов юзера в Манзану
            $updateManzanaResult = $this->doManzanaUpdateContact($user);
            if ($updateManzanaResult->isSuccess()) {
                // с Манзаной все сложилось, коммит данных в БД
                $this->connection->commitTransaction();

                // отправка юзеру sms о регистрации на сайте
                if ($this->arParams['SEND_USER_REGISTRATION_SMS'] === 'Y') {
                    $smsResult = $this->sendUserRegistrationSms($user);
                }
            } else {
                // в Манзану данные не ушли - откатываемся
                $this->connection->rollbackTransaction();
                $result->addError(
                    new Error('Не удалось передать данные в Manzana Loyalty', 'createUserManzanaError')
                );
            }
        } else {
            // откатываем транзакцию БД
            $this->connection->rollbackTransaction();
        }

        $result->setData(
            [
                'user' => $user,
                'updateManzanaResult' => $updateManzanaResult,
                'smsResult' => $smsResult,
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
        $fields = $this->convertUserToArray(
            $this->userByFormFields()
        );

        return $this->updateUser($userId, $fields);
    }

    /**
     * @param int $userId
     * @param array $fields
     * @return Result
     */
    protected function updateUser(int $userId, array $fields)
    {
// !!!
// Проблема: манзане нужен железно номер телефона
// !!!
        $result = new Result();
        $this->connection->startTransaction();
        try {
            $updateResult = $this->getUserRepository()->updateData($userId, $fields);
            if (!$updateResult) {
                $result->addError(
                    new Error('Нераспознанная ошибка', 'updateUserUnknownError')
                );
            }
        } catch (\Exception $exception) {
            $result->addError(
                new Error($exception->getMessage(), 'updateUserException')
            );

            $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
        }

        $updateManzanaResult = null;
        if ($result->isSuccess()) {
            // отправка контактов юзера в Манзану
            $user = $this->convertUserFromArray($fields);
            $updateManzanaResult = $this->doManzanaUpdateContact($user);
            if ($updateManzanaResult->isSuccess()) {
                // с Манзаной все сложилось, коммит данных в БД
                $this->connection->commitTransaction();
            } else {
                // в Манзану данные не ушли - откатываемся
                $this->connection->rollbackTransaction();
                $result->addError(
                    new Error('Не удалось передать данные в Manzana Loyalty', 'updateUserManzanaError')
                );
            }
        } else {
            // откатываем транзакцию БД
            $this->connection->rollbackTransaction();
        }

        $result->setData(
            [
                'userId' => $userId,
                'fields' => $fields,
                'updateManzanaResult' => $updateManzanaResult,
            ]
        );

        return $result;
    }

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

        if (!strlen($phone)) {
            $result->addError(
                new Error('Не задан телефон для отправки SMS', 'sendSmsEmptyPhone')
            );
        }
        if (!strlen($text)) {
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
     * @param User $user
     * @return Result
     */
    protected function doManzanaUpdateContact(User $user)
    {
        $result = new Result();
        $pnone = $user->getNormalizePersonalPhone();
        if (!strlen($pnone)) {
            $result->addError(
                new Error('Не задан телефон для отправки данных в Manzana Loyalty', 'manzanaUpdateContactEmptyPhone')
            );
        }

        $manzanaClient = null;

        if ($result->isSuccess()) {
            $manzanaService = $this->getManzanaService();
            $contactId = '';
            try {
                $contactId = $manzanaService->getContactIdByPhone($pnone);
            } catch (ManzanaServiceContactSearchNullException $exception) {
                // контакта с заданным номером телефона в Манзане нет - создаем
                $this->log()->debug(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'manzanaUpdateContactException')
                );
                $this->log()->error(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
            }

            if ($result->isSuccess()) {
                try {
                    $manzanaClient = new Client();
                    $this->getUserService()->setClientPersonalDataByCurUser($manzanaClient, $user);
                    if (strlen($contactId)) {
                        $manzanaClient->contactId = $contactId;
                    }
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
                'manzanaClient' => $manzanaClient
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
        if (strlen($phone)) {
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
        if (strlen($email)) {
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
        if (strlen($cardNumber)) {
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
