<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class FrontOfficeUserGroupRights20180208150000 extends SprintMigrationBase
{
    const GROUP_CODE = 'FRONT_OFFICE_USERS';
    const TASK_LETTER = 'G';

    protected $description = 'Добавление уровня доступа "G" главного модуля и установка данного уровня группе пользователей "Заполнение анкет перерегистрации карт"';

    public function up()
    {
        $result = false;
        $userGroupHelper = $this->getHelper()->UserGroup();
        $curGroup = $userGroupHelper->getGroup(static::GROUP_CODE);
        if (!$curGroup) {
            $this->log()->error('Группа пользователей '.static::GROUP_CODE.' не найдена');
        } else {
            $taskId = 0;
            $item = \CTask::GetList(
                [],
                [
                    'LETTER' => static::TASK_LETTER,
                    'MODULE_ID' => 'main',
                    'BINDING' => 'module',
                    'SYS' => 'N'
                ]
            )->Fetch();
            if ($item) {
                $taskId = $item['ID'];
            } else {
                $taskId = \CTask::Add(
                    [
                        'LETTER' => static::TASK_LETTER,
                        'MODULE_ID' => 'main',
                        'BINDING' => 'module',
                        'NAME' => 'Просмотр списка пользователей разрешенных групп (view_subordinate_users)'
                    ]
                );
            }

            if ($taskId) {
                $setOperations = ['view_own_profile', 'view_subordinate_users'];
                $operationsList = \CTask::GetOperations($taskId, true);
                $continue = false;
                if (!$operationsList) {
                    \CTask::SetOperations($taskId, $setOperations, true);
                    $continue = true;
                } else {
                    if (count(array_intersect($setOperations, $operationsList)) != count($setOperations)) {
                        $this->log()->error('Уровень доступа '.static::TASK_LETTER.' уже занят');
                    } else {
                        $continue = true;
                    }
                }
                if ($continue) {
                    \CGroup::SetModulePermission($curGroup['ID'], 'main', $taskId);
                    $this->log()->info('Установлен уровень доступа '.static::TASK_LETTER.' для группы '.static::GROUP_CODE);

                    $registeredUsersGroup = $userGroupHelper->getGroup('REGISTERED_USERS');
                    if ($registeredUsersGroup) {
                        \CGroup::SetSubordinateGroups($curGroup['ID'], [$registeredUsersGroup['ID']]);
                        $this->log()->info('Установлены подчиненные группы пользователей для группы '.static::GROUP_CODE);
                    }
                    $result = true;
                }
            } else {
                $this->log()->error('Не удалось создать уровень доступа '.static::GROUP_CODE);
            }
        }

        return $result;
    }

    public function down()
    {
        return true;
    }
}
