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
 * Class AddTypeToConfirmCode20180221131640
 *
 * @package Sprint\Migration
 */
class AddTypeToConfirmCode20180221131640 extends SprintMigrationBase
{
    protected $description = 'Добавление колонки типа в таблицу';
    
    /**
     * @throws SqlQueryException
     * @return bool|void
     */
    public function up()
    {
        $connection = Application::getConnection();
        /** @noinspection PhpUnhandledExceptionInspection */
        $connection->queryExecute("ALTER TABLE 4lp_ConfirmCode ADD TYPE VARCHAR(10) DEFAULT 'sms' NOT NULL");
    }

    public function down()
    {
    }
}
