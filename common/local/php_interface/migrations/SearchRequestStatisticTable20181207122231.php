<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;

class SearchRequestStatisticTable20181207122231 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "Таблица для сбора статистики поисковых запросов";

    public function up()
    {
        $helper = new HelperManager();

        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_search_request_statistic`;');
            Application::getConnection()->query('
                CREATE TABLE `4lapy_search_request_statistic` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `search_string` VARCHAR(255) NOT NULL,
                    `quantity` INT NOT NULL DEFAULT 0,
                    `last_date_search` DATETIME NOT NULL,
                    PRIMARY KEY (`id`)
                )'
            );
            return true;
        } catch (SqlQueryException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function down()
    {
        $helper = new HelperManager();
        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_search_request_statistic`;');
            return true;
        } catch (SqlQueryException $e) {
            echo $e->getMessage();
            return false;
        }
    }

}
