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
 * Class ConfirmCodeHash20190625205840
 *
 * @package Sprint\Migration
 */
class ConfirmCodeHash20190625205840 extends SprintMigrationBase
{
    protected $description = 'Добавление поля с md5-хэшем в таблицу 4lp_confirm_code';
    
    /**
     * @throws ArgumentException
     * @throws SqlQueryException
     * @return bool|void
     */
    public function up()
    {
        $connection = Application::getConnection();
        if ($connection->isTableExists('4lp_confirm_code')) {
            $connection->queryExecute("ALTER TABLE `4lp_confirm_code`   
  ADD COLUMN `HASH` VARCHAR(32) NULL AFTER `TYPE`;");
        }
    }
}
