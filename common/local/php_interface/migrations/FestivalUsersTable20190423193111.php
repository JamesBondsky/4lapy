<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;

class FestivalUsersTable20190423193111 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = "Таблица для получения номеров регистрации на фестиваль";

    public function up()
    {
        //$helper = new HelperManager();
        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_festival_users`;');
            Application::getConnection()->query('
                CREATE TABLE `4lapy_festival_users` (
                    id INT NOT NULL AUTO_INCREMENT,
                    hash VARCHAR(32) NOT NULL,
                    date_insert DATETIME DEFAULT NOW() NOT NULL,
                    PRIMARY KEY (id),
                    INDEX (hash)
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
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_festival_users`;');
            return true;
        } catch (SqlQueryException $e) {
            echo $e->getMessage();
            return false;
        }
    }
}
