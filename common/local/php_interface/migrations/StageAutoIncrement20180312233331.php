<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;
use FourPaws\App\Env;
use Sprint\Migration\Exceptions\MigrationException;

class StageAutoIncrement20180312233331 extends SprintMigrationBase
{
    protected $description = 'Устанавливаем AutoIncrement для Stage зоны';

    /**
     * @throws SqlQueryException
     * @return bool
     */
    public function up()
    {
        if (Env::isStage()) {
            $increment = date('Ymd') + date('His');
            $connection = Application::getConnection();
            $connection->startTransaction();
            try {
                $connection->query('ALTER TABLE b_user AUTO_INCREMENT = ' . $increment);
                $connection->query('ALTER TABLE b_sale_order AUTO_INCREMENT = ' . $increment);
                $connection->query('ALTER TABLE b_sale_basket AUTO_INCREMENT = ' . $increment);
                $connection->query('ALTER TABLE b_sale_fuser AUTO_INCREMENT = ' . $increment);
                $connection->commitTransaction();
            } catch (SqlQueryException $e) {
                $connection->rollbackTransaction();
                return false;
            }
        }
        return true;
    }

    /**
     * @throws MigrationException
     * @return bool
     */
    public function down()
    {
        if (Env::isStage()) {
            throw new MigrationException('Cant down autoincrement');
        }
        return true;
    }
}
