<?php

namespace Sprint\Migration;


use Bitrix\Main\Application;
use FourPaws\SocServ\CSocServOK2;

class OkChangeAuthType20200128113240 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Изменяет тип авторизации через ок на новый кастомный";

    public function up(){
        $helper = new HelperManager();

        //your code ...
        try {
            Application::getConnection()->query('UPDATE ' . \Bitrix\Socialservices\UserTable::getTableName() . ' SET EXTERNAL_AUTH_ID="' . CSocServOK2::ID . '" WHERE EXTERNAL_AUTH_ID="' . \CSocServOdnoklassniki::ID . '"');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function down(){
        $helper = new HelperManager();

        //your code ...
        try {
            Application::getConnection()->query('UPDATE ' . \Bitrix\Socialservices\UserTable::getTableName() . ' SET EXTERNAL_AUTH_ID="' . \CSocServOdnoklassniki::ID . '" WHERE EXTERNAL_AUTH_ID="' . CSocServOK2::ID . '"');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
