<?php


namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\MobileApiBundle\Tables\UserApiLastUsingTable;
use Bitrix\Main\Application;

class UserApiLastUsing20190719124221 extends SprintMigrationBase
{
    protected $description = 'Добавлена новая таблица - сохраняет время последнего обращения к апи,3 необходимо для выгрузки к манзане';

    public function up()
    {
        $tableName = UserApiLastUsingTable::getTableName();
        $tableStructure = <<<SQL
CREATE TABLE `$tableName`(
`ID`                   INT          NOT NULL AUTO_INCREMENT,
`USER_ID`              INT,
`DATE_INSERT`          DATETIME     NOT NULL,
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
        $tableName = UserApiLastUsingTable::getTableName();
        Application::getConnection()->dropTable($tableName);
    }
}
