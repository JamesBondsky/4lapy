<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;

class OrderNumberTable20190307062911 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = "Таблица для получения новых номеров заказов";

    public function up()
    {
        //$helper = new HelperManager();
        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_order_number`;');
            Application::getConnection()->query('
                CREATE TABLE `4lapy_order_number` (
                    ACCOUNT_NUMBER INT NOT NULL AUTO_INCREMENT,
                    PRIMARY KEY (ACCOUNT_NUMBER)
                );'
            );
            Application::getConnection()->query('ALTER TABLE 4lapy_order_number AUTO_INCREMENT = 2730000;');

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
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_order_number`;');
            return true;
        } catch (SqlQueryException $e) {
            echo $e->getMessage();
            return false;
        }
    }
}
