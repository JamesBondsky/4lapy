<?php

namespace Sprint\Migration;


use FourPaws\MobileApiBundle\Tables\ApiPushEventTable;
use Bitrix\Main\Application;
use FourPaws\MobileApiBundle\Tables\ApiUserSessionTable;
use Adv\Bitrixtools\Migration\SprintMigrationBase;

class ApiPushEventAddUserIdCol20190527134251 extends SprintMigrationBase
{

    protected $description = "Добавляем столбец USER_ID табличку api_push_event для АПИ мобильного приложения";

    public function up()
    {
        $tableName = ApiPushEventTable::getTableName();
        $query = <<<SQL
ALTER TABLE `$tableName`
ADD COLUMN USER_ID INT(11);
SQL;
        Application::getConnection()->startTransaction();
        try {
            Application::getConnection()->query($query);
            Application::getConnection()->commitTransaction();
        } catch (\Exception $exception) {
            Application::getConnection()->rollbackTransaction();
            throw new \RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
            return false;
        }

        try {
            $arPushTokens = [];
            $dbResult = ApiPushEventTable::getList([
                'select' => [
                    'ID',
                    'PUSH_TOKEN'
                ]
            ]);
            while ($pushEvent = $dbResult->fetch()) {
                $arPushTokens[$pushEvent['ID']] = $pushEvent['PUSH_TOKEN'];
            }

            $arUserSessions = [];
            $dbResult = ApiUserSessionTable::getList([
                'select' => [
                    'USER_ID',
                    'PUSH_TOKEN'
                ],
                'filter' => [
                    'PUSH_TOKEN' => $arPushTokens
                ]
            ]);

            while ($arUserSession = $dbResult->fetch()) {
                $arUserSessions[$arUserSession['PUSH_TOKEN']] = $arUserSession['USER_ID'];
            }

            foreach($arPushTokens as $id => $pushToken){
                ApiPushEventTable::update($id, ['USER_ID' => $arUserSessions[$pushToken]]);
            }

            return true;
        } catch (\Exception $exception) {
            throw new \RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
            return false;
        }
    }

    public function down()
    {
        $tableName = ApiPushEventTable::getTableName();
        $query = <<<SQL
ALTER TABLE `$tableName`
DROP COLUMN USER_ID;
SQL;
        Application::getConnection()->startTransaction();
        try {
            Application::getConnection()->query($query);
            Application::getConnection()->commitTransaction();
            return true;
        } catch (\Exception $exception) {
            Application::getConnection()->rollbackTransaction();
            throw  new \RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
            return false;
        }
    }
}
