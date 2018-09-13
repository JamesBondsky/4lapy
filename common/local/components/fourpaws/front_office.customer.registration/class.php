<?php

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\FrontOffice\Bitrix\Component\CustomerRegistration;
use FourPaws\FrontOffice\Traits\SmsTrait;
use FourPaws\UserBundle\Entity\User;
use JMS\Serializer\Serializer;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsFrontOfficeCustomerRegistrationComponent extends CustomerRegistration
{
    use SmsTrait;

    /** @var Serializer $serializer */
    protected $serializer;

    /**
     * \FourPawsFrontOfficeCustomerRegistrationComponent constructor.
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
            if ($this->request->get('formName') === 'customerRegistration') {
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
            $this->processPhoneNumber();
            $this->processPersonalData();
            $this->processEmail();
            if (empty($this->arResult['ERROR']['FIELD'])) {
                if ($this->trimValue($this->getFormFieldValue('doCustomerRegistration')) === 'Y') {
                    $registrationResult = $this->doCustomerRegistration();
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
                $users = $this->searchAllUsersByPhoneNumber($phone);
                if ($users) {
                    $this->setAlreadyRegisteredUsers($users);
                    $this->setFieldError(
                        $fieldName,
                        'Данный телефонный номер есть в базе данных сайта',
                        'already_registered'
                    );
                } else {
                    $searchResult = $this->getUserDataByPhone($phone);
                    if (!$searchResult->isSuccess()) {
                        $this->setFieldError($fieldName, $searchResult->getErrors(), 'runtime');
                    } else {
                        $searchResultData = $searchResult->getData();
                        if ($searchResultData['clients']) {
                            foreach ($searchResultData['clients'] as $clientData) {
                                $this->arResult['CONTACT_DATA']['USER'][] = [
                                    'CONTACT_ID' => $clientData['CONTACT_ID'],
                                    'LAST_NAME' => $clientData['LAST_NAME'],
                                    'FIRST_NAME' => $clientData['FIRST_NAME'],
                                    'SECOND_NAME' => $clientData['SECOND_NAME'],
                                    'BIRTHDAY' => $clientData['BIRTHDAY'],
                                    'PHONE' => $clientData['PHONE'],
                                    'EMAIL' => $clientData['EMAIL'],
                                    'GENDER_CODE' => $clientData['GENDER_CODE'],
                                    '_PHONE_NORMALIZED_' => $this->cleanPhoneNumberValue($clientData['PHONE']),
                                    '_BX_GENDER_CODE_' => $this->getBitrixGenderByExternalGender($clientData['GENDER_CODE']),
                                ];
                            }
                        }
                    }
                }
            } else {
                $this->setFieldError($fieldName, 'Номер телефона задан некорректно', 'not_valid');
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

    /**
     * @throws ApplicationCreateException
     */
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
     * @return bool
     */
    protected function canSendUserRegistrationSms()
    {
        return $this->arParams['SEND_USER_REGISTRATION_SMS'] === 'Y';
    }

    /**
     * @param int $userId
     */
    protected function setRegisteredUserId(int $userId)
    {
        $this->arResult['REGISTERED_USER_ID'] = $userId > 0 ? $userId : 0;
    }

    /**
     * @param User[] $users
     */
    protected function setAlreadyRegisteredUsers(array $users)
    {
        $this->arResult['ALREADY_REGISTERED_USERS'] = $users;
    }

    /**
     * @return Result
     * @throws ApplicationCreateException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function doCustomerRegistration()
    {
        $result = new Result();

        $phone = $this->cleanPhoneNumberValue($this->getFormFieldValue('phone'));
        if ($phone === '') {
            $result->addError(
                new Error('Не задан номер телефона', 'emptyPhoneField')
            );
        }

        $resultData = [];
        $userId = 0;
        $user4Sms = null;

        // создание пользователя на сайте
        if ($result->isSuccess()) {
            $createResult = $this->createUserByFormFields();
            if (!$createResult->isSuccess()) {
                $result->addErrors($createResult->getErrors());
            } else {
                /** @var User $tmpCreatedUser */
                $tmpCreatedUser = $createResult->getData()['user'];
                $userId = (int)$tmpCreatedUser->getId();

                if ($this->canSendUserRegistrationSms()) {
                    // для sms нужно брать объект юзера от createResult, т.к. в нем хранится пароль
                    $user4Sms = clone $tmpCreatedUser;
                }
            }
            $resultData['createUserResult'] = $createResult;
        }

        $this->setRegisteredUserId($userId);

        // для Манзаны берем карточку из базы (нужны все поля)
        $user4Manzana = null;
        if ($result->isSuccess()) {
            $user4Manzana = $this->searchUserById($userId);
            if (!$user4Manzana) {
                $result->addError(
                    new Error(
                        'Не найден пользователь по id: '.$userId,
                        'doCustomerRegistrationUserNotFound'
                    )
                );
                // раз пользователь в базе не найден, то не нужно и sms слать
                $user4Sms = null;
            }
        }

        // Отправка актуальных данных юзера в Манзану (обновление или создание контакта)
        $contact = null;
        if ($result->isSuccess()) {
            $updateManzanaResult = null;
            try {
                $updateManzanaResult = $this->doManzanaUpdateContact($user4Manzana);
                if (!$updateManzanaResult->isSuccess()) {
                    $result->addErrors($updateManzanaResult->getErrors());
                } else {
                    $contact = $updateManzanaResult->getData()['resultContact'];
                }
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'manzanaUpdateContactException')
                );
            }
            $resultData['manzanaUpdateContactResult'] = $updateManzanaResult;
        }

        // Привязка карты к юзеру
        if ($contact instanceof Client) {
            $this->bindUserDiscountCard($userId, $contact);
        }

        // Отправка юзеру sms о регистрации на сайте
        if ($user4Sms && $this->canSendUserRegistrationSms()) {
            $resultData['smsSendResult'] = $this->sendUserRegistrationSms(
                $user4Sms,
                $this->arParams['REGISTRATION_SMS_TEXT']
            );
        }

        $result->setData($resultData);

        return $result;
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
     * @return Result
     * @throws ApplicationCreateException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\FrontOffice\Exception\InvalidArgumentException
     */
    protected function doManzanaUpdateContact(User $user)
    {
        try {
            $currentContact = $this->getManzanaIntegrationService()->getContactByPhone(
                $user->getManzanaNormalizePersonalPhone()
            );
        } catch (\FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException $exception) {
            // если найдено несколько контактов, то создаем еще один
            $currentContact = null;
        }

        $result = $this->getManzanaIntegrationService()->updateContact(
            $user,
            [
                // Код места активации карты
                'shopOfActivation' => $this->getShopOfActivation(),
                // Код места регистрации карты (от юзера, заданного в параметрах компонента определяется)
                'shopRegistration' => $this->getShopRegistration(),
                // автоматическая установка флага актуальности контакта
                'setActualContact' => $this->shouldSetActualContact(),
            ],
            $currentContact
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
     * @param int $userId
     * @param Client $contact
     * @throws ApplicationCreateException
     */
    protected function bindUserDiscountCard(int $userId, Client $contact)
    {
        if ($contact->contactId) {
            $actualCard = $this->getManzanaIntegrationService()->getActualCardByContactId(
                $contact->contactId
            );
            $cardNumber = $actualCard ? trim($actualCard->cardNumber) : '';
            if ($cardNumber !== '') {
                $res = $this->getUserService()->getUserRepository()->updateDiscountCard(
                    $userId,
                    $cardNumber
                );
                if ($res) {
                    $this->clearUserTaggedCache($userId);
                }
            }
        }
    }
}
