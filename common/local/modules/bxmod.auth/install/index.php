<?php
#################################
#   Developer: Lynnik Danil     #
#   Site: http://bxmod.ru       #
#   E-mail: support@bxmod.ru    #
#################################

IncludeModuleLangFile(__FILE__);

Class bxmod_auth extends CModule
{
    var $MODULE_ID = "bxmod.auth";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $MODULE_GROUP_RIGHTS = "Y";

    function bxmod_auth()
    {
        $arModuleVersion = array();

        $path = str_replace( "\\", "/", __FILE__ );
        $path = substr( $path, 0, strlen($path) - strlen("/index.php") );
        include( $path."/version.php" );

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        
        $this->PARTNER_NAME = GetMessage("BXMOD_AUTH_PARTNER_NAME");
        $this->PARTNER_URI = "http://bxmod.ru";

        $this->MODULE_NAME = GetMessage("BXMOD_AUTH_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("BXMOD_AUTH_MODULE_DESCRIPTION");
    }
    
    function InstallDB()
    {
        global $DB;
        
        $DB->Query("DROP TABLE IF EXISTS `b_bxmod_auth_smscontrol`", true);
        $DB->Query("CREATE TABLE `b_bxmod_auth_smscontrol` ( `phone` varchar(20) NOT NULL DEFAULT '', `sendTime` int(11) DEFAULT NULL, PRIMARY KEY (`phone`) ) ENGINE=InnoDB DEFAULT CHARSET=cp1251;", true);

        RegisterModule($this->MODULE_ID);
        return true;
    }
    
    function UnInstallDB()
    {
        global $DB;
        
        $DB->Query("DROP TABLE `b_bxmod_auth_smscontrol`", true);
        
        UnRegisterModule($this->MODULE_ID);
        return true;
    }

    function InstallEvents()
    {
        return true;
    }

    function UnInstallEvents()
    {
        return true;
    }

    function InstallFiles()
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/". $this->MODULE_ID ."/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/". $this->MODULE_ID ."/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools", true, true);
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/components/bxmod/auth.dialog");
        DeleteDirFilesEx("/bitrix/tools/bxmod_auth.php");
        return true;
    }

    function DoInstall()
    {
        global $APPLICATION;

        if (!IsModuleInstalled($this->MODULE_ID))
        {
            $this->InstallDB();
            $this->InstallEvents();
            $this->InstallFiles();
        }
    }

    function DoUninstall()
    {
        $this->UnInstallDB();
        $this->UnInstallEvents();
        $this->UnInstallFiles();
    }
}
?>