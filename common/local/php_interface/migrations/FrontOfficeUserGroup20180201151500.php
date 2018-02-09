<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class FrontOfficeUserGroup20180201151500 extends SprintMigrationBase
{
    const GROUP_CODE = 'FRONT_OFFICE_USERS';
    const SEARCH_GROUP_NAME = 'Заполнение анкет перерегистрации карт';

    protected $description = 'Добавление группе пользователей "Заполнение анкет перерегистрации карт" символьного кода FRONT_OFFICE_USERS';

    public function up()
    {
        $result = false;
        $userGroupHelper = $this->getHelper()->UserGroup();
        $curGroup = $userGroupHelper->getGroup(static::GROUP_CODE);
        if ($curGroup) {
            $result = true;
            $this->log()->info('Группа с символьным кодом '.static::GROUP_CODE.' уже существует.');
        } else {
            $groups = $userGroupHelper->getGroupsByFilter(['NAME' => static::SEARCH_GROUP_NAME]);
            $groupObj = new \CGroup();
            $curGroup = count($groups) == 1 ? reset($groups) : [];
            $curGroup = $curGroup && $curGroup['NAME'] == static::SEARCH_GROUP_NAME ? $curGroup : [];
            if ($curGroup) {
                if ($groupObj->Update($curGroup['ID'], ['STRING_ID' => static::GROUP_CODE])) {
                    $result = true;
                    $this->log()->info('Группе пользователей id:'.$curGroup['ID'].' установлен символьный код: '.static::GROUP_CODE);
                } else {
                    $this->log()->error('Ошибка изменения группы пользователей id:'.$curGroup['ID']);
                }
            } else {
                $newGroupId = $groupObj->Add(
                    [
                        'STRING_ID' => static::GROUP_CODE,
                        'NAME' => 'Пользователи ЛК магазина',
                        'ACTIVE' => 'Y',
                        'C_SORT' => '7000',
                    ]
                );
                if ($newGroupId) {
                    $result = true;
                    $this->log()->info('Создана группа пользователей id:'.$newGroupId);
                } else {
                    $this->log()->error('Ошибка создания группы пользователей '.static::GROUP_CODE);
                }
            }
        }

        return $result;
    }

    public function down()
    {
        return true;
    }
}
