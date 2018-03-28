<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Catalog\MeasureTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class CatalogMeasure20180328150301 extends SprintMigrationBase
{
    protected $description = 'Установка дефолтной единицы измерения товарам каталога, где она не задана';

    /**
     * @return bool
     * @throws ArgumentException
     * @throws SqlQueryException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function up()
    {
        $defaultMeasure = MeasureTable::getList(['filter' => ['IS_DEFAULT' => 'Y']])->fetch();
        if (!$defaultMeasure) {
            $this->log()->error('Не найдена единица измерений по умолчанию');
            return false;
        }

        Application::getConnection()
            ->query('
                UPDATE
                    b_catalog_product
                SET MEASURE = \'' . $defaultMeasure['ID'] . '\'
                WHERE MEASURE = 0 OR MEASURE IS NULL'
            );
        return true;
    }

    public function down()
    {
        /**
         * не требуется
         */
    }
}
