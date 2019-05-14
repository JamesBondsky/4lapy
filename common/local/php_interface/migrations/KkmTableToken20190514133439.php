<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;

class KkmTableToken20190514133439 extends SprintMigrationBase
{
    const TOKENS = [
        'aCKcteDReRTE922M', //sandbox
        'eVkj7uhtLO0bE7hG', //preprod
        'd4oXgzn5tl0tMXYB' //prod
    ];

    protected $description = "Добавляет таблицу для хранения токенов";

    public function up()
    {
        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_kkm_token`;');
            Application::getConnection()->query('
                CREATE TABLE `4lapy_kkm_token` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `token` CHAR(16) NOT NULL,
                    PRIMARY KEY (`id`)
                )'
            );

            Application::getConnection()->query('ALTER TABLE `4lapy_kkm_token` ADD INDEX `ix_token` (`token`);');

            foreach (static::TOKENS as $token) {
                Application::getConnection()->query('INSERT INTO `4lapy_kkm_token` (token) VALUES (\'' . $token . '\');');
            }

            return true;
        } catch (SqlQueryException $e) {
            return false;
        }
    }

    public function down()
    {
        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_kkm_token`;');
            return true;
        } catch (SqlQueryException $e) {
            return false;
        }
    }
}
