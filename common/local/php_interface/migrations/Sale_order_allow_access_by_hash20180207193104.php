<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Config\Option;

class Sale_order_allow_access_by_hash20180207193104 extends SprintMigrationBase
{
    protected $description = 'Задание настройки модуля интернет-магазина, разрешающей доступ к заказу по его хешу';

    public function up()
    {
        Option::set('sale', 'allow_guest_order_view', 'Y');
        Option::set('sale', 'allow_guest_order_view_status', serialize(['N']));
    }

    public function down()
    {
        Option::set('sale', 'allow_guest_order_view', 'N');
        Option::set('sale', 'allow_guest_order_view_status', serialize([]));
    }
}
