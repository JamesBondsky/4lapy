<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;

class UserPersonalPhoneAddIndex20180712131315 extends SprintMigrationBase
{
    protected $description = 'Добавление индекса на поле "Телефон" в таблице пользователей';

    public function up()
    {
        Application::getConnection()->query('
            ALTER TABLE b_user ADD INDEX IX_PERSONAL_PHONE (PERSONAL_PHONE)'
        );

        return true;
    }

    public function down()
    {

    }
}