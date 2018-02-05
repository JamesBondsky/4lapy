<?php

use Adv\Bitrixtools\Tools\Main\UserGroupUtils;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\UserUtils;
use FourPaws\App\Application;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\UserService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsFrontOfficeAvatarComponent extends \CBitrixComponent
{
    use LazyLoggerAwareTrait;

    /** код группы пользователей, имеющих доступ к компоненту, если ничего не задано в параметрах подключения */
    const DEFAULT_USER_GROUP_CODE = 'FRONT_OFFICE_USERS';

    /** @var string $action */
    private $action = '';
    /** @var UserService $userCurrentUserService */
    private $userCurrentUserService;
    /** @var string $canAccess */
    protected $canAccess = '';

    public function __construct($component = null)
    {
        // LazyLoggerAwareTrait не умеет присваивать имя по классам без неймспейса
        // делаем это вручную
        $this->logName = __CLASS__;

        parent::__construct($component);
    }

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
            $params['USER_ID'] = $GLOBALS['USER']->getId();
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
     * @return bool
     */
    protected function canAccess()
    {
        if ($this->canAccess === '') {
            $this->canAccess = 'N';
            if ($GLOBALS['USER']->isAdmin()) {
                $this->canAccess = 'Y';
            } else {
                if ($this->arParams['USER_ID'] != $GLOBALS['USER']->getId()) {
                    $userGroups = UserUtils::getGroupIds($this->arParams['USER_ID']);
                } else {
                    $userGroups = $GLOBALS['USER']->getUserGroupArray();
                }
                if (array_intersect($this->arParams['USER_GROUPS'], $userGroups)) {
                    $this->canAccess = 'Y';
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

        if ($this->request->get('action') === 'postForm')  {
            if ($this->request->get('formName') === 'avatar') {
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
     * @return UserService
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
     */
    public function getUserRepository()
    {
        return $this->getUserService()->getUserRepository();
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

    protected function initialLoadAction()
    {
        $this->loadData();
    }

    protected function postFormAction()
    {
        $this->initPostFields();

        if ($this->canAccess()) {
            $this->processSearchFormFields();
        }

        $this->loadData();
    }

    protected function processSearchFormFields()
    {
        $fieldsList = [
            'cardNumber', 'phone',
            'firstName', 'secondName', 'lastName',
            'birthDay'
        ];

        $isEmptyForm = true;
        foreach ($fieldsList as $fieldName) {
            $value = $this->trimValue($this->getFormFieldValue($fieldName));
            if ($value !== '') {
                $isEmptyForm = true;
                break;
            }
        }
        if ($isEmptyForm) {
            $this->setExecError('emptyForm', 'Не указаны данные для поиска', 'emptyForm');
            return;
        }

        $fieldName = 'cardNumber';
        $cardNumber = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($cardNumber !== '' && strlen($cardNumber) != 13) {
            $this->setFieldError($fieldName, 'Неверный номер карты', 'incorrect_value');
        }

        $fieldName = 'birthDay';
        $value = $this->trimValue($this->getFormFieldValue($fieldName));
        if ($value !== '') {
            if(!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value)) {
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
