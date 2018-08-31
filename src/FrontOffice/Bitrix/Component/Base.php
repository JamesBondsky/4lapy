<?php

namespace FourPaws\FrontOffice\Bitrix\Component;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Error;
use FourPaws\FrontOffice\Traits\EnvUserAccessTrait;
use FourPaws\FrontOffice\Traits\UserServiceTrait;
use FourPaws\Helpers\PhoneHelper;

abstract class Base extends \CBitrixComponent
{
    use LazyLoggerAwareTrait;
    use UserServiceTrait;
    use EnvUserAccessTrait;

    /** код группы пользователей, имеющих доступ к компоненту (по умолчанию) */
    const CAN_ACCESS_USER_GROUP_CODE_DEFAULT = 'FRONT_OFFICE_USERS';

    /** операции, к одной из которых у пользователя должен быть доступ (по умолчанию) */
    const CAN_ACCESS_USER_OPERATIONS_DEFAULT = [];

    /** ID админской группы */
    const BX_ADMIN_GROUP_ID = 1;

    /** @var string $action */
    protected $action = '';

    /**
     * Base constructor.
     *
     * @param null|\CBitrixComponent $component
     */
    public function __construct($component = null)
    {
        // LazyLoggerAwareTrait не умеет присваивать имя по классам без неймспейса
        // делаем это вручную
        $this->withLogName(__CLASS__);

        parent::__construct($component);
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

        $this->setAdminGroupId(static::BX_ADMIN_GROUP_ID);

        // пользователь, от имени которого запущен функционал
        $params['USER_ID'] = isset($params['USER_ID']) ? (int)$params['USER_ID'] : 0;
        if ($params['USER_ID'] <= 0) {
            $params['USER_ID'] = (int)$GLOBALS['USER']->getId();
        }
        $this->setEnvUserId($params['USER_ID']);

        // группы пользователей, имеющих доступ к функционалу
        if (!isset($params['CAN_ACCESS_USER_GROUPS']) || !is_array($params['CAN_ACCESS_USER_GROUPS'])) {
            $params['CAN_ACCESS_USER_GROUPS'] = [];
        }
        if (empty($params['CAN_ACCESS_USER_GROUPS']) && static::CAN_ACCESS_USER_GROUP_CODE_DEFAULT) {
            $defaultGroupId = $this->getGroupIdByCode(static::CAN_ACCESS_USER_GROUP_CODE_DEFAULT);
            if ($defaultGroupId) {
                $params['CAN_ACCESS_USER_GROUPS'][] = $defaultGroupId;
            }
        }
        $this->setCanAccessUserGroups($params['CAN_ACCESS_USER_GROUPS']);

        // операции, открывающие доступ к функционалу (проверяются только если заданы)
        if (!isset($params['CAN_ACCESS_USER_OPERATIONS']) || !is_array($params['CAN_ACCESS_USER_OPERATIONS'])) {
            $params['CAN_ACCESS_USER_OPERATIONS'] = [];
        }
        if (empty($params['CAN_ACCESS_USER_OPERATIONS']) && static::CAN_ACCESS_USER_OPERATIONS_DEFAULT) {
            $params['CAN_ACCESS_USER_OPERATIONS'] = static::CAN_ACCESS_USER_OPERATIONS_DEFAULT;
        }
        $this->setCanAccessUserOperations($params['CAN_ACCESS_USER_OPERATIONS']);

        $params['CACHE_TYPE'] = $params['CACHE_TYPE'] ?? 'A';
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 3600;

        $params = parent::onPrepareComponentParams($params);

        return $params;
    }

    /**
     * @throws \Exception
     */
    public function executeComponent()
    {
        try {
            $this->setAction(
                $this->prepareAction()
            );
            $this->doAction();
        } catch (\Exception $exception) {
            $this->log()->critical(
                sprintf(
                    '%s exception: %s',
                    __FUNCTION__,
                    $exception->getMessage()
                )
            );
            throw $exception;
        }
    }

    /**
     * @return string
     */
    abstract protected function prepareAction();

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

    protected function doAction()
    {
        $action = $this->getAction();
        if (is_callable(array($this, $action.'Action'))) {
            call_user_func(array($this, $action.'Action'));
        }
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
     * @param string $errName
     * @param array|string $errorMsg
     * @param string $errCode
     */
    protected function setExecError(string $errName, $errorMsg, $errCode = '')
    {
        $errorMsg = $this->prepareErrorMsg($errorMsg);
        $this->arResult['ERROR']['EXEC'][$errName] = new Error($errorMsg, $errCode);
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
}
