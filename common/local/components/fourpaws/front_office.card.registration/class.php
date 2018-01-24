<?php

use Bitrix\Main\Error;
use FourPaws\App\Application;
use FourPaws\External\ManzanaService;

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

        $this->loadData();
    }

    public function loadData()
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
            try {
                /** @var FourPaws\External\Manzana\Model\CardValidateResult $validateResult */
                $validateResult = $this->getManzanaService()->validateCardByNumber($value);
                if (intval($validateResult->validationResultCode) === 2) {
                    /** @var FourPaws\External\Manzana\Model\Card $card */
                    $card = $this->getManzanaService()->searchCardByNumber($value);
                    if ($card) {
                        if ($card->hashChildrenCode === 200000) {
                            // если HasChildrenCode=200000, анкета считается актуальной
                            $this->arResult['CARD_DATA']['IS_ACTUAL_PROFILE'] = 'Y';
                        } else {
                            $this->arResult['CARD_DATA']['IS_ACTUAL_PROFILE'] = 'N';
                            $this->arResult['CARD_DATA']['IS_ACTUAL_PHONE'] = 'N';
                            $this->arResult['CARD_DATA']['IS_ACTUAL_EMAIL'] = 'N';
                        }
                        // товарищи из манзаны гарантируют: ненулевой pl_debet <=> карта бонусная
                        $this->arResult['CARD_DATA']['IS_BONUS_CARD'] = doubleval($card->plDebet) > 0 ? 'Y' : 'N';

                        if ($card->familyStatusCode === 2) {
                            $this->setFieldError($fieldName, 'Card activated', 'activated');
                        }

                        $this->arResult['CARD_DATA']['USER'] = [
                            'CONTACT_ID' => htmlspecialcharsbx(trim($card->contactId)),
                            'LAST_NAME' => htmlspecialcharsbx(trim($card->lastName)),
                            'FIRST_NAME' => htmlspecialcharsbx(trim($card->firstName)),
                            'SECOND_NAME' => htmlspecialcharsbx(trim($card->secondName)),
                            'BIRTHDAY' => $card->birthDate ? $card->birthDate->format('d.m.Y') : '',
                            'PHONE' => htmlspecialcharsbx(trim($card->phone)),
                            'EMAIL' => htmlspecialcharsbx(trim($card->email)),
                            'GENDER_CODE' => intval($card->genderCode),
                        ];
                    }
                }

                if (empty($this->arResult['CARD_DATA'])) {
                    $this->setFieldError($fieldName, 'Not found', 'not_found');
                }
            } catch (\Exception $exception) {
                $this->setFieldError($fieldName, $exception->getMessage(), 'exception');
            }
        }
    }

    protected function processPersonalData()
    {
        $tmpList = [
            'lastName' => 'last name',
            'firstName' => 'name',
            'secondName' => 'second name',
        ];
        foreach ($tmpList as $fieldName => $caption) {
            $value = $this->trimValue($this->getFormFieldValue($fieldName));
            if (!strlen($value)) {
                $this->setFieldError($fieldName, 'Undefined '.$caption, 'empty');
            } else {
                if (preg_match('/[^а-яА-ЯёЁ\-\s]/u', $value)) {
                    $this->setFieldError($fieldName, 'Not valid '.$caption, 'not_valid');
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
            $first = substr($value, 0, 1);
            if($first != 9 || !preg_match('/^[0-9]{10,10}+$/', $value)) {
                $this->setFieldError($fieldName, 'Not valid', 'not_valid');
            } else {
                if ($this->getUserByPhoneNumber($value)) {
                    $this->setFieldError($fieldName, 'Already registered phone number', 'already_registered');
                }
            }
        }
    }

    protected function cleanPhoneNumberValue($phone)
    {
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
            $phone = '7'.substr($phone, 1, 3).substr($phone, 4, 3).substr($phone, 7);
            return $phone;
        }
        return '';
    }

    protected function getUserByPhoneNumber($phone)
    {
        $user = [];
        $phone = $this->cleanPhoneNumberValue($phone);
        if ($phone) {
            $searchPhone = substr($phone, 1);

            // ищем пользователя с таким телефоном в БД
            $items = \CUser::GetList(
                $by = 'ID',
                $order = 'ASC',
                [
                    'PERSONAL_PHONE' => $searchPhone,
                    'PERSONAL_PHONE_EXACT_MATCH' => 'Y'
                ],
                [
                    'FIELDS' => [
                        'ID', 'EMAIL'
                    ]
                ]
            );
            while ($item = $items->Fetch() ) {
                if (strpos($item['EMAIL'], '@fastorder.ru') === false) {
                    $user = $item;
                    break;
                }
            }
        }
        return $user;
    }

    protected function processEmail()
    {
        $fieldName = 'email';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if (!strlen($value)) {
            $this->setFieldError($fieldName, 'Undefined email', 'empty');
        } else {
            // to do
        }
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
