<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;

class CatalogAvailable20180328120116 extends SprintMigrationBase
{
    protected $description = 'Установка параметра AVAILABLE товарам каталога';

    public function up()
    {
        Application::getConnection()
            ->query('
                UPDATE
                    b_catalog_product
                SET AVAILABLE = \'Y\''
            );
    }

    public function down()
    {
        /**
         * не требуется
         */
    }
}
