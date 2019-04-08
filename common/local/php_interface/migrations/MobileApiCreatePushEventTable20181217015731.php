<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use FourPaws\MobileApiBundle\Tables\ApiPushEventTable;

class MobileApiCreatePushEventTable20181217015731 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Создание служебной таблички api_push_event для пуш уведомлений в мобильном апи';

    public function up()
    {
        $tableName = ApiPushEventTable::getTableName();
        /**
         * Compile from d7 DataManager will return only not null table fields structure
         */
        $tableStructure = <<<SQL
CREATE TABLE IF NOT EXISTS TABLE `$tableName`(
  `ID`                       INT          NOT NULL AUTO_INCREMENT,
  `PLATFORM`                 CHAR(1)      NOT NULL,
  `PUSH_TOKEN`               VARCHAR(255) NOT NULL DEFAULT 0,
  `DATE_TIME_EXEC`           DATETIME     NOT NULL,
  `MESSAGE_ID`               INT          NOT NULL,
  `SUCCESS_EXEC`             CHAR(1)      NOT NULL DEFAULT 'W',
  `VIEWED`                   TINYINT      NOT NULL DEFAULT 0,
  `MD5`                      VARCHAR(255),
  `SERVICE_RESPONSE_STATUS`  INT,
  `SERVICE_RESPONSE_ERROR`   VARCHAR(255),
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
            throw  new \RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function down()
    {
        $tableName = ApiPushEventTable::getTableName();
        Application::getConnection()->dropTable($tableName);
    }

}
