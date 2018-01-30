<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use FourPaws\MobileApiBundle\Tables\ApiUserSessionTable;

class ApiSessionForeignKey20180130120856 extends SprintMigrationBase
{
    public function __construct()
    {
        parent::__construct();
        $this->description = 'Add Foreign key to Api Session';
    }


    /**
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @return bool
     */
    public function up(): bool
    {
        Application::getConnection()
            ->query('ALTER TABLE api_user_session ADD FOREIGN KEY (FUSER_ID) REFERENCES b_sale_fuser(ID) ON DELETE CASCADE');
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
