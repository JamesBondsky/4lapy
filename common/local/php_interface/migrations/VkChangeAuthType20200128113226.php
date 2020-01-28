<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use FourPaws\SocServ\CSocServVK2;


class VkChangeAuthType20200128113226 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Изменяет тип авторизации через вк на новый кастомный";

    public function up(){
        $helper = new HelperManager();

        //your code ...
        try {
            Application::getConnection()->query('UPDATE ' . \Bitrix\Socialservices\UserTable::getTableName() . ' SET EXTERNAL_AUTH_ID="' . CSocServVK2::ID . '" WHERE EXTERNAL_AUTH_ID="' . \CSocServVKontakte::ID . '"');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function down(){
        $helper = new HelperManager();

        //your code ...

        try {
            Application::getConnection()->query('UPDATE ' . \Bitrix\Socialservices\UserTable::getTableName() . ' SET EXTERNAL_AUTH_ID="' . \CSocServVKontakte::ID . '" WHERE EXTERNAL_AUTH_ID="' . CSocServVK2::ID . '"');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
