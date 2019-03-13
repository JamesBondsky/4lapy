<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;

class OrderNumberTableAddOrderIdField20190313090611 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = "Добавление поля ORDER_ID в таблицу с номерами заказов";

    public function up()
    {
        //$helper = new HelperManager();
        try {
            Application::getConnection()->query('alter table `4lapy_order_number` add ORDER_ID int null;');
            Application::getConnection()->query('create index `4lapy_order_number_ORDER_ID_uindex` on `4lapy_order_number` (ORDER_ID);');

            return true;
        } catch (SqlQueryException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function down()
    {
        //$helper = new HelperManager();
        try {
            Application::getConnection()->query('drop index `4lapy_order_number_ORDER_ID_uindex` on `4lapy_order_number`;');
            Application::getConnection()->query('alter table `4lapy_order_number` drop column ORDER_ID;');
            return true;
        } catch (SqlQueryException $e) {
            echo $e->getMessage();
            return false;
        }
    }
}
