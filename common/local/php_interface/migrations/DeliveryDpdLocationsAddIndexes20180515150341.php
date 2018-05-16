<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;

class DeliveryDpdLocationsAddIndexes20180515150341 extends SprintMigrationBase
{
    protected $description = 'Добавление индексов в таблицу местоположений DPD';

    public function up()
    {
        Application::getConnection()->query('
                ALTER TABLE `b_ipol_dpd_location`
                    ADD INDEX `IX_CITY_ID` (`CITY_ID`),
                    ADD INDEX `IX_CITY_CODE` (`CITY_CODE`),
                    ADD INDEX `IX_LOCATION_ID` (`LOCATION_ID`)
            '
        );
    }

    public function down()
    {

    }
}