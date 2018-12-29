<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;

class SmsPasswordForgotPhoneTable20181229142111 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "Восстановление пароля таблица для проверки номеров";

    public function up()
    {
        $helper = new HelperManager();

        try {
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_sms_protector`;');
            Application::getConnection()->query('
                CREATE TABLE `4lapy_sms_protector` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `phone` INT NOT NULL,
                    `date` DATETIME NOT NULL,
                    PRIMARY KEY (`id`)
                )'
            );
            Application::getConnection()->query('ALTER TABLE `4lapy_sms_protector` ADD  INDEX `phone` (`phone`);');
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
            Application::getConnection()->query('DROP TABLE IF EXISTS `4lapy_sms_protector`;');
            return true;
        } catch (SqlQueryException $e) {
            echo $e->getMessage();
            return false;
        }

    }

}
