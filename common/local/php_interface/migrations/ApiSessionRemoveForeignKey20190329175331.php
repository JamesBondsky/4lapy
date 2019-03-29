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
        Application::getConnection()
            ->query('ALTER TABLE api_user_session DROP FOREIGN KEY api_user_session_ibfk_1');
    }

    public function down(){
        // no down
    }

}
