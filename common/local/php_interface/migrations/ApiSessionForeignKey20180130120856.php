<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;

class ApiSessionForeignKey20180130120856 extends SprintMigrationBase
{
    public function __construct()
    {
        parent::__construct();
        $this->description = 'Modify fields api session table and add foreign keys';
    }


    /**
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @return bool
     */
    public function up(): bool
    {
        Application::getConnection()
            ->query('ALTER TABLE api_user_session ADD FOREIGN KEY (FUSER_ID) REFERENCES b_sale_fuser(ID) ON DELETE CASCADE');
        Application::getConnection()
            ->query('ALTER TABLE api_user_session MODIFY DATE_UPDATE DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        Application::getConnection()
            ->query('ALTER TABLE api_user_session MODIFY DATE_INSERT DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
        return parent::up();
    }

    /**
     * @throws \RuntimeException
     * @return bool|void
     */
    public function down()
    {
        parent::down();
        throw new \RuntimeException('No down (:');
    }
}
