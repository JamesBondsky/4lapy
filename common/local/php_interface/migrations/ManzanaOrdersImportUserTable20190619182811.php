<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;

class ManzanaOrdersImportUserTable20190619182811 extends SprintMigrationBase
{
    protected $description = 'Таблица юзеров в очереди на импорт заказов из Manzana';

    public function up()
    {
        //$helper = new HelperManager();
        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_manzana_orders_import_user`;');
            Application::getConnection()->query('
                CREATE TABLE `4lapy_manzana_orders_import_user`(
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `user_id` INT NOT NULL,
                    `datetime_insert` DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE INDEX (`user_id`)
                );'
            );

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
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_manzana_orders_import_user`;');
            return true;
        } catch (SqlQueryException $e) {
            echo $e->getMessage();
            return false;
        }
    }
}