<?php

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use FourPaws\App\Application;
use FourPaws\External\ManzanaService;
use FourPaws\UserBundle\Service\UserService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\UserBundle\Entity\User;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsFrontOfficeCardRegistrationComponent extends \CBitrixComponent
{
    const GENDER_CODE_M = 1;
    const GENDER_CODE_F = 2;
    private $action = '';
    /** @var ManzanaService $manzanaService */
    private $manzanaService;
    /** @var UserService $userCurrentUserService */
    private $userCurrentUserService;

    /*
    public function __construct($component = null)
    {
        parent::__construct($component);
    }
    */

    public function onPrepareComponentParams($params)
    {
        $params['CACHE_TYPE'] = isset($params['CACHE_TYPE']) ? $params['CACHE_TYPE'] : 'A';
        $params['CACHE_TIME'] = isset($params['CACHE_TIME']) ? $params['CACHE_TIME'] : 3600;

        $params = parent::onPrepareComponentParams($params);

        return $params;
    }

    public function executeComponent()
    {
        $this->setAction($this->prepareAction());
        $this->doAction();
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

    protected function getManzanaService()
    {
        if (!$this->manzanaService) {
            $this->manzanaService = Application::getInstance()->getContainer()->get('manzana.service');
        }
        return $this->manzanaService;
    }

    protected function getUserRepository()
    {
        if (!$this->userCurrentUserService) {
            $this->userCurrentUserService = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
        }
        return $this->userCurrentUserService->getUserRepository();
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
            if ($this->searchUserByCardNumber($value)) {
                $this->setFieldError($fieldName, 'Card activated', 'activated');
            } else {
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
            if($value != static::GENDER_CODE_M && $value != static::GENDER_CODE_F) {
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
            return $result;
        }

        $cardNumber = $this->trimValue($this->getFormFieldValue('cardNumber'));
        if (!strlen($phone)) {
            $result->addError(
                new Error('Не задан номер карты', 'emptyCardNumberField')
            );
            return $result;
        }

        if ($result->isSuccess()) {
            $users = $this->getUserListByParams(
                [
                    'filter' => [
                        [
                            'LOGIC' => 'OR',
                            [
                                '=PERSONAL_PHONE' => $phone
                            ],
                            [
                                '=UF_DISCOUNT_CARD' => $cardNumber
                            ],
// !!!
// Надо ли проверять @fastorder?
// !!!
                        ]
                    ]
                ]
            );

            foreach ($users as $user) {
                $cardNumberUser = trim($user->getDiscountCardNumber());
                $phoneUser = $this->cleanPhoneNumberValue($user->getPersonalPhone());
                $updateUser = null;
                if ($phoneUser == $phone) {
                    // Если найден пользователь с указанным телефоном без привязанной бонусной карты,
                    // бонусная карта привязывается к профилю пользователя.
                    // Если найден пользователь с указанным телефоном и другим номером бонусной карты,
                    // данные о номере бонусной карты обновляются в профиле пользователя.
                    if (!strlen($cardNumberUser) || $cardNumberUser != $cardNumber) {
                        // обновляем профиль
                        //$updateUser = clone $user;
                        $updateUser = new User();
                        $updateUser->setDiscountCardNumber($cardNumber);
                    }
                } elseif ($cardNumberUser == $cardNumber) {
                    // Если найден пользователь с указанным номером бонусной карты без номера телефона или
                    // с другим номером телефона, данные о номере телефона обновляются в профиле пользователя.
                    // Бонусная карта привязывается к профилю пользователя
                    if (!strlen($phoneUser) || $phoneUser != $phone) {
                        //$updateUser = clone $user;
                        $updateUser = new User();
                        $updateUser->setPersonalPhone($phone);
                    }
                }
                if ($updateUser) {
                    $updateUser->setId($user->getId());
                    $updateResult = $this->updateUser($updateUser);
                    if (!$updateResult->isSuccess()) {
                        $result->addErrors($updateResult->getErrors());
                    }
                }
            }

            if (!$users) {
                // Если пользователь не найден, Система создает новую учетную запись пользователя
                // с указанными личными данными.
                // Бонусная карта привязывается к профилю пользователя.
                $newUser = new User();
                $addResult = $this->addUser($newUser);
                if (!$addResult->isSuccess()) {
                    $result->addErrors($addResult->getErrors());
                }
            }
        }
        return $result;
    }

    public function cleanPhoneNumberValue($phone)
    {
        /*
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $first = substr($phone, 0, 1);
        $second = substr($phone, 1, 1);
        $correct = false;
        if (strlen($phone) == 10 && $first == 9) {
            $phone = '7'.$phone;
            $correct = true;
        } elseif (strlen($phone) == 11 && ($first == 8 || $first == 7) && $second == 9) {
            $correct = true;
        }
        if ($correct) {
            //$phone = '7'.substr($phone, 1, 3).substr($phone, 4, 3).substr($phone, 7);
            $phone = substr($phone, 1, 3).substr($phone, 4, 3).substr($phone, 7);
            return $phone;
        }
        return '';
        */
        try {
            $phone = PhoneHelper::normalizePhone($phone);
        } catch (\Exception $exception) {
            $phone = '';
        }
        return $phone;
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

        /*
        if(isset($filter['=PERSONAL_PHONE'])) {
            $filter['PERSONAL_PHONE_EXACT_MATCH'] = 'Y';
            $filter['PERSONAL_PHONE'] = $filter['=PERSONAL_PHONE'];
            unset($filter['=PERSONAL_PHONE']);
        }

        $sortBy = 'ID';
        $sortOrder = 'ASC';
        if (isset($params['order'])) {
            foreach ($params['order'] as $key => $value) {
                $sortBy = $key;
                $sortOrder = $value;
                break;
            }
        }

        $select = [
            'FIELDS' => [
                'ID', 'EMAIL',
                'PERSONAL_PHONE',
                'UF_DISCOUNT_CARD',
            ]
        ];
        if (isset($params['limit'])) {
            $select['NAV_PARAMS'] = [
                'nTopCount' => intval($params['limit']),
            ];
        }
        if (isset($params['select'])) {
            $select['FIELDS'] = $params['select'];
        }

        $users = [];
        $items = \CUser::GetList($sortBy, $sortOrder, $filter, $select);
        while ($item = $items->Fetch()) {
            $users[] = $item;
        }
        */
        return $users;
    }

    /**
     * @param User $user
     * @return Result
     */
    protected function updateUser(User $user)
    {
        $result = new Result();
        try {
            $this->getUserRepository()->update($user);
        } catch (\Exception $exception) {
            $result->addError(
                new Error($exception->getMessage(), 'updateUser')
            );
        }

        return $result;
    }

    /**
     * @param User $user
     * @return Result
     */
    protected function addUser(User $user)
    {
        $result = new Result();
        try {
            $this->getUserRepository()->create($user);
        } catch (\Exception $exception) {
            $result->addError(
                new Error($exception->getMessage(), 'addUser')
            );
        }

        return $result;
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
                        '=PERSONAL_PHONE' => $phone,
                    ]
                ]
            );
            foreach ($items as $item) {
// !!!
// Это еще актуально?
// Если актуально, то, возможно, нужно непосредственно в FourPaws\UserBundle\Entity\User релизовать метод
// !!!
                if (strpos($item->getEmail(), '@fastorder.ru') === false) {
                    $user = $item;
                    break;
                }
            }
        }
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
                        '=EMAIL' => $email,
                    ],
                    'limit' => 1
                ]
            );
            $user = reset($items);
        }
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
                        '=UF_DISCOUNT_CARD' => $cardNumber,
                    ],
                    'limit' => 1
                ]
            );
            $user = reset($items);
        }
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
