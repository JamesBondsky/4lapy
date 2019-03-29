<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;

class ApiSessionRemoveForeignKey20190329175331 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Удаление каскада для корректной работы сессий в МП";

    /**
     * @return bool|void
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public function up(){
        $data = Application::getConnection()
            ->query('SELECT CONSTRAINT_NAME FROM `information_schema`.`referential_constraints` WHERE TABLE_NAME = "api_user_session"')
            ->fetch();

        if ($data) {
            Application::getConnection()
                ->query('ALTER TABLE api_user_session DROP FOREIGN KEY ' . $data['CONSTRAINT_NAME']);
        }
    }

    public function down(){
        // no down
    }

}
