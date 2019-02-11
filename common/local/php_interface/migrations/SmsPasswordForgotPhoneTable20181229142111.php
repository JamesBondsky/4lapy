<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;

class SmsPasswordForgotPhoneTable20181229142111 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "Таблицы для защиты смс от накруток";

    public function up()
    {
        $helper = new HelperManager();

        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_sms_log`;');
            Application::getConnection()->query('
                CREATE TABLE `4lapy_sms_log` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `phone` BIGINT NOT NULL,
                    `date` DATETIME NOT NULL,
                    PRIMARY KEY (`id`)
                )'
            );
            Application::getConnection()->query('ALTER TABLE `4lapy_sms_log` ADD  INDEX `phone` (`phone`);');
            Application::getConnection()->query('ALTER TABLE `4lapy_sms_log` ADD  INDEX `date` (`phone`);');


            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_sms_quarantine`;');
            Application::getConnection()->query('
                CREATE TABLE `4lapy_sms_quarantine` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `phone` BIGINT NOT NULL,
                    `date_from` DATETIME NOT NULL,
                    `date_to` DATETIME NOT NULL,
                    PRIMARY KEY (`id`)
                )'
            );
            Application::getConnection()->query('ALTER TABLE `4lapy_sms_quarantine` ADD  INDEX `phone` (`phone`);');
            Application::getConnection()->query('ALTER TABLE `4lapy_sms_quarantine` ADD  INDEX `date_to` (`phone`);');

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
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_sms_log`;');
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_sms_quarantine`;');
            return true;
        } catch (SqlQueryException $e) {
            echo $e->getMessage();
            return false;
        }

    }

}
