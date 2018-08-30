<?php

namespace FourPaws\FrontOffice\Traits;

use Adv\Bitrixtools\Tools\Main\UserGroupUtils;

trait EnvUserAccessTrait
{
    /** @var int ID админской группы пользователей */
    private $bxAdminGroupId = 1;

    /** @var int ID группы, в которую входят все пользователи, в т.ч. неавторизованные */
    private $bxAllUsersGroupId = 2;

    /** @var array ID групп пользователей, имющих доступ к функционалу */
    private $canAccessUserGroups = [];

    /** @var array $canAccessOperations Список операций, открывающих доступ к функционалу. Если не задан, то не проверяется */
    private $canAccessOperations = [];

    /** @var int $envUserId */
    private $envUserId = 0;

    /** @var string $envUserCanAccess */
    private $envUserCanAccess = '';

    /** @var array $envUserGroups */
    private $envUserGroups = null;

    /** @var bool $isEnvUserAdmin */
    private $isEnvUserAdmin = null;

    /** @var array $envUserOperations */
    private $envUserOperations = null;

    /** @var array $envUserSubordinateGroups */
    private $envUserSubordinateGroups = null;

    protected function flushEnvUserAccessData()
    {
        $this->envUserCanAccess = '';
        $this->isEnvUserAdmin = null;
        $this->envUserGroups = null;
        $this->envUserOperations = null;
        $this->envUserSubordinateGroups = null;
    }

    /**
     * @param int $userId
     */
    protected function setEnvUserId(int $userId)
    {
        if ($userId > 0) {
            $this->envUserId = $userId;
            $this->flushEnvUserAccessData();
        }
    }

    /**
     * @return int
     */
    protected function getEnvUserId()
    {
        return $this->envUserId;
    }

    /**
     * @param array $groups
     */
    protected function setCanAccessUserGroups(array $groups)
    {
        $setGroups = [];
        foreach ($groups as $groupId) {
            $groupId = (int)$groupId;
            if ($groupId > 0) {
                $setGroups[] = $groupId;
            }
        }

        if ($setGroups) {
            $this->canAccessUserGroups = array_unique($setGroups);
            $this->flushEnvUserAccessData();
        }
    }

    /**
     * @return array
     */
    protected function getCanAccessUserGroups()
    {
        return $this->canAccessUserGroups;
    }

    /**
     * @param array $operations
     */
    protected function setCanAccessUserOperations(array $operations)
    {
        $setOperations = [];
        foreach ($operations as $op) {
            $op = trim($op);
            if ($op !== '') {
                $setOperations[] = $op;
            }
        }

        if ($setOperations) {
            $this->canAccessOperations = array_unique($setOperations);
            $this->flushEnvUserAccessData();
        }
    }

    /**
     * @return array
     */
    protected function getCanAccessUserOperations()
    {
        return $this->canAccessOperations;
    }

    /**
     * @param int $groupId
     */
    protected function setAdminGroupId(int $groupId)
    {
        if ($groupId > 0) {
            $this->bxAdminGroupId = $groupId;
            $this->flushEnvUserAccessData();
        }
    }

    /**
     * @return int
     */
    protected function getAdminGroupId()
    {
        return $this->bxAdminGroupId;
    }

    /**
     * @return array
     */
    protected function getEnvUserGroups()
    {
        if ($this->envUserGroups === null) {
            $this->envUserGroups = [];
            $userId = $this->getEnvUserId();
            if ($userId > 0) {
                $this->envUserGroups = \CUser::GetUserGroup($userId);
            }
            // группа "все пользователи"
            $this->envUserGroups[] = $this->bxAllUsersGroupId;
            $this->envUserGroups = array_unique($this->envUserGroups);
        }

        return $this->envUserGroups;
    }

    /**
     * @return bool
     */
    protected function isEnvUserAdmin()
    {
        if ($this->isEnvUserAdmin === null) {
            $this->isEnvUserAdmin = in_array($this->getAdminGroupId(), $this->getEnvUserGroups());
        }

        return $this->isEnvUserAdmin;
    }

    /**
     * @return bool
     */
    protected function canEnvUserAccess()
    {
        if ($this->envUserCanAccess === '') {
            $this->envUserCanAccess = 'N';

            if ($this->isEnvUserAdmin()) {
                $this->envUserCanAccess = 'Y';
            } else {
                $userGroups = $this->getEnvUserGroups();
                $canAccessGroups = array_merge($this->getCanAccessUserGroups(), [$this->getAdminGroupId()]);
                if (array_intersect($canAccessGroups, $userGroups)) {
                    $canAccessOperations = $this->getCanAccessUserOperations();
                    if ($canAccessOperations) {
                        // заданы операции, к одной из которых у юзера должен быть доступ
                        foreach ($canAccessOperations as $operationName) {
                            if ($this->canEnvUserDoOperation($operationName)) {
                                $this->envUserCanAccess = 'Y';
                                break;
                            }
                        }
                    } else {
                        $this->envUserCanAccess = 'Y';
                    }
                }
            }
        }

        return $this->envUserCanAccess === 'Y';
    }

    /**
     * @return array
     */
    protected function getEnvUserOperations()
    {
        if ($this->envUserOperations === null) {
            $userGroups = $this->getEnvUserGroups();
            $this->envUserOperations = $userGroups ? array_keys($GLOBALS['USER']->GetAllOperations($userGroups)) : [];
        }

        return $this->envUserOperations;
    }

    /**
     * @param string $operationName
     * @return bool
     */
    protected function canEnvUserDoOperation(string $operationName)
    {
        $result = false;
        if ($this->isEnvUserAdmin()) {
            $result = true;
        }
        if (!$result) {
            $result = in_array($operationName, $this->getEnvUserOperations());
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getEnvUserSubordinateGroups()
    {
        if ($this->envUserSubordinateGroups === null) {
            $this->envUserSubordinateGroups = [];
            $userOperations = $this->getEnvUserOperations();
            if (!in_array('edit_all_users', $userOperations) && !in_array('view_all_users', $userOperations)) {
                $userGroups = $this->getEnvUserGroups();
                if ($userGroups) {
                    $this->envUserSubordinateGroups = \CGroup::GetSubordinateGroups($userGroups);
                }
            }
        }

        return $this->envUserSubordinateGroups;
    }

    /**
     * @param string $groupCode
     * @return int
     */
    protected function getGroupIdByCode(string $groupCode)
    {
        $groupId = 0;
        try {
            $groupId = UserGroupUtils::getGroupIdByCode($groupCode);
        } catch (\Exception $exception) {
            // не нашли и ладно
        }

        return $groupId;
    }
}
