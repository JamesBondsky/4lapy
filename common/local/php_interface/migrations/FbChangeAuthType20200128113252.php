<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;

use FourPaws\SocServ\CSocServFB2;

class FbChangeAuthType20200128113252 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Изменяет тип авторизации через фб на новый кастомный";

    public function up(){
        $helper = new HelperManager();

        //your code ...
        try {
            Application::getConnection()->query('UPDATE ' . \Bitrix\Socialservices\UserTable::getTableName() . ' SET EXTERNAL_AUTH_ID="' . CSocServFB2::ID . '" WHERE EXTERNAL_AUTH_ID="' . \CSocServFacebook::ID . '"');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function down(){
        $helper = new HelperManager();

        //your code ...
        try {
            Application::getConnection()->query('UPDATE ' . \Bitrix\Socialservices\UserTable::getTableName() . ' SET EXTERNAL_AUTH_ID="' . \CSocServFacebook::ID . '" WHERE EXTERNAL_AUTH_ID="' . CSocServFB2::ID . '"');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
