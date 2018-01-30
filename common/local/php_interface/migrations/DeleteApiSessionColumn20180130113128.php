<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use FourPaws\MobileApiBundle\Tables\ApiUserSessionTable;

class DeleteApiSessionColumn20180130113128 extends SprintMigrationBase
{
    public function __construct()
    {
        parent::__construct();
        $this->description = 'Удаление не нужного столбца в таблице сессий Api';
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public function up()
    {
        Application::getConnection()->query('ALTER TABLE ' . ApiUserSessionTable::getTableName() . ' DROP COLUMN USER_ID');
        return parent::up();
    }

    /**
     * @return bool|void
     * @throws \RuntimeException
     */
    public function down()
    {
        parent::down();
        throw new \RuntimeException('No down (:');
    }
}
