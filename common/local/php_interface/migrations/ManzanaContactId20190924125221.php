<?php


namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\MobileApiBundle\Tables\ManzanaContactIdTable;
use Bitrix\Main\Application;

class ManzanaContactId20190924125221 extends SprintMigrationBase
{
    protected $description = 'Добавлена новая таблица - сохраняет contact id из манзаны';

    public function up()
    {
        $tableName = ManzanaContactIdTable::getTableName();

        $tableStructure = <<<SQL
CREATE TABLE `$tableName`(
`ID`                   BIGINT(20) NOT NULL AUTO_INCREMENT,
`USER_PHONE`           BIGINT(20) NOT NULL,
`CONTACT_DATA`         TEXT NOT NULL,
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
        $tableName = ManzanaContactIdTable::getTableName();
        Application::getConnection()->dropTable($tableName);
    }
}
