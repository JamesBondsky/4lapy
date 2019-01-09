<?php

namespace Sprint\Migration;


use FourPaws\MobileApiBundle\Tables\ApiUserSessionTable;
use Bitrix\Main\Application;

class ApiUserSessionAddPlatformAndPushTokenCols20190105134251 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляем столбцы PLATFORM и PUSH_TOKEN в табличку api_user_session для АПИ мобильного приложения";

    public function up(){
        $tableName = ApiUserSessionTable::getTableName();
        $query = <<<SQL
ALTER TABLE `$tableName`
ADD COLUMN PLATFORM VARCHAR(10),
ADD COLUMN PUSH_TOKEN VARCHAR(255);
SQL;
        Application::getConnection()->startTransaction();
        try {
            Application::getConnection()->query($query);
            Application::getConnection()->commitTransaction();
        } catch (\Exception $exception) {
            Application::getConnection()->rollbackTransaction();
            throw  new \RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
        }

    }

    public function down(){
        $tableName = ApiUserSessionTable::getTableName();
        $query = <<<SQL
ALTER TABLE `$tableName`
DROP COLUMN PLATFORM,
DROP COLUMN PUSH_TOKEN;
SQL;
        Application::getConnection()->startTransaction();
        try {
            Application::getConnection()->query($query);
            Application::getConnection()->commitTransaction();
        } catch (\Exception $exception) {
            Application::getConnection()->rollbackTransaction();
            throw  new \RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
        }

    }

}
