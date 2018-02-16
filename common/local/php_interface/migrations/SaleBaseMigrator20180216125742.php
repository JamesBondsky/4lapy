<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\Migrator\Client\SaleBasePull;

class SaleBaseMigrator20180216125742 extends SprintMigrationBase
{

    protected $description = 'Импорт статусов, служб доставки со старого сайта. Маппинг свойств заказа на старом и новом сайтах.';

    public function up()
    {
        $pull = new SaleBasePull();

        $pull->save();
    }

    public function down()
    {
        /**
         * Нет необходимости в откате
         */
    }

}
