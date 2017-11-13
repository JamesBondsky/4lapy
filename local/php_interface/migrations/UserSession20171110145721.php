<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use FourPaws\MobileApiBundle\Tables\UserSessionTable;

class UserSession20171110145721 extends SprintMigrationBase
{
    public function __construct()
    {
        $this->description = 'Create User Session Table for mobile api';
        parent::__construct();
    }

    public function up()
    {
        $tableName = UserSessionTable::getTableName();
        /**
         * Compile from d7 DataManager will return only not null table fields structure
         */
        $tableStructure = <<<SQL
CREATE TABLE `$tableName` (
  `ID`          INT          NOT NULL AUTO_INCREMENT,
  `DATE_INSERT` DATETIME     NOT NULL,
  `DATE_UPDATE` DATETIME     NOT NULL,
  `USER_ID`     INT,
  `USER_AGENT`  VARCHAR(255),
  `FUSER_ID`    INT          NOT NULL,
  `TOKEN`       VARCHAR(255) NOT NULL,
  PRIMARY KEY (`ID`)
)
SQL;

        Application::getConnection()->startTransaction();
        if (Application::getConnection()->isTableExists($tableName)) {
            throw new \RuntimeException(sprintf(
                'Table %s already exists',
                $tableName
            ));
        }
        try {
            Application::getConnection()->query($tableStructure);
            Application::getConnection()->commitTransaction();
        } catch (\Exception $exception) {
            Application::getConnection()->rollbackTransaction();
        }
    }

    public function down()
    {
        $tableName = UserSessionTable::getTableName();
        Application::getConnection()->dropTable($tableName);
    }
}
