<?php

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\FrontOffice\Bitrix\Component\CustomerRegistration;
use FourPaws\FrontOffice\Traits\SmsTrait;
use FourPaws\UserBundle\Entity\User;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsFrontOfficeCardRegistrationComponent extends CustomerRegistration
{
    use SmsTrait;

    /** @var Serializer $serializer */
    protected $serializer;

    /**
     * FourPawsFrontOfficeCardRegistrationComponent constructor.
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

        if (isset($params['SEND_USER_REGISTRATION_SMS']) && $params['SEND_USER_REGISTRATION_SMS'] === 'N') {
            $params['SEND_USER_REGISTRATION_SMS'] = 'N';
        } else {
            $params['SEND_USER_REGISTRATION_SMS'] = 'Y';
        }

        $params['REGISTRATION_SMS_TEXT'] = $params['REGISTRATION_SMS_TEXT'] ?? '';
        if (!$params['REGISTRATION_SMS_TEXT']) {
            $params['REGISTRATION_SMS_TEXT'] = '';
            // без переносов строк
            $params['REGISTRATION_SMS_TEXT'] .= 'Спасибо за регистрацию на сайте 4lapy.ru!';
            $params['REGISTRATION_SMS_TEXT'] .= ' Теперь Вам доступны все возможности личного кабинета! Номер вашего телефона является логином, пароль для доступа #PASSWORD#.';
            $params['REGISTRATION_SMS_TEXT'] .= ' Для авторизации перейдите по ссылке https://4lapy.ru/personal/.';
        }

        if (isset($params['SHOP_OF_ACTIVATION'])) {
            $params['SHOP_OF_ACTIVATION'] = trim($params['SHOP_OF_ACTIVATION']);
        } else {
            $params['SHOP_OF_ACTIVATION'] = 'UpdatedByСassa';
        }

        return $params;
    }

    /**
     * @throws Exception
     */
    public function executeComponent()
    {
        parent::executeComponent();
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

    protected function initialLoadAction()
    {
        $this->loadData();
    }

    /**
     * @throws ApplicationCreateException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function postFormAction()
    {
        $this->initPostFields();

        if ($this->canEnvUserAccess()) {
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
        $this->arResult['CAN_ACCESS'] = $this->canEnvUserAccess() ? 'Y' : 'N';
        $this->arResult['ACTION'] = $this->getAction();
        $this->includeComponentTemplate();
    }

    /**
     * @throws ApplicationCreateException
     */
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
                    $this->setFieldError(
                        $fieldName,
                        'Пользователь с заданным e-mail уже зарегистрирован',
                        'already_registered'
                    );
                }
            }
        }
    }

    /**
     * @return Result
     * @throws ApplicationCreateException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
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
                    // для sms нужно брать объект юзера от createResult, т.к. в нем хранится пароль
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
                        $resultData['createUserResults'][0]['sms'] = $this->sendUserRegistrationSms(
                            $userSms,
                            $this->arParams['REGISTRATION_SMS_TEXT']
                        );
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
     * @param int $userId
     * @return Result
     */
    protected function updateUserByFormFields(int $userId)
    {
        $fields = $this->userArrayByFormFields();

        return $this->updateUser($userId, $fields);
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
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
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
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function doManzanaUpdateContact(User $user)
    {
        $result = $this->getManzanaIntegrationService()->updateContactByUserPhone(
            $user,
            [
                // Код места активации карты
                'shopOfActivation' => $this->getShopOfActivation(),
                // Код места регистрации карты (от юзера, заданного в праметрах компонента определяется)
                'shopRegistration' => $this->getShopRegistration(),
                // автоматическая установка флага актуальности контакта
                'setActualContact' => $this->shouldSetActualContact(),
            ]
        );

        return $result;
    }
}
