<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;

class CatalogCanBuyZero20180326190921 extends SprintMigrationBase
{
    protected $description = 'Установка параметра CAN_BUY_ZERO товарам каталога';

    protected const OPTION_NAME = 'default_can_buy_zero';

    public function up()
    {
        Option::set('catalog', 'default_can_buy_zero', 'Y');

        Application::getConnection()
            ->query('
                UPDATE 
                    b_catalog_product 
                SET CAN_BUY_ZERO = \'Y\''
            );
    }

    public function down()
    {
        Option::set('catalog', 'default_can_buy_zero', 'N');
    }
}
