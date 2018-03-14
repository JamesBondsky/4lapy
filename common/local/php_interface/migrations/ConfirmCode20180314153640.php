<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\StringField;

/**
 * Class ConfirmCode20180314153640
 *
 * @package Sprint\Migration
 */
class ConfirmCode20180314153640 extends SprintMigrationBase
{
    protected $description = 'увеличение размера поля тип до 50 символов в таблице конфирм и переименование';
    
    /**
     * @throws ArgumentException
     * @throws SqlQueryException
     * @return bool|void
     */
    public function up()
    {
        $connection = Application::getConnection();
        if ($connection->isTableExists('4lp_ConfirmCode')) {
            $connection->queryExecute("ALTER TABLE `4lp_ConfirmCode` MODIFY TYPE VARCHAR(50) NOT NULL DEFAULT 'sms'");
            $connection->renameTable('4lp_ConfirmCode', '4lp_confirm_code');
        }
    }
}
