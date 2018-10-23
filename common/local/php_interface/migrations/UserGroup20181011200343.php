<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use FourPaws\Enum\UserGroup;

class UserGroup20181011200343 extends SprintMigrationBase
{
    protected $description = 'Добавление всем пользователям группы "Зарегистрированные"';

    public function up()
    {
        Application::getConnection()->query(
            \sprintf(
                'INSERT INTO b_user_group (GROUP_ID, USER_ID)
                    SELECT %s AS GROUP_ID,
                           u.ID AS USER_ID
                    FROM b_user u
                    WHERE NOT EXISTS
                        (SELECT *
                         FROM b_user_group ug
                         WHERE ug.USER_ID = u.ID
                           AND ug.GROUP_ID = %s)',
                UserGroup::REGISTERED_USERS,
                UserGroup::REGISTERED_USERS
            )
        );
    }

    public function down()
    {

    }
}
