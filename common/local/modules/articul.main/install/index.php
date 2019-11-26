<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\IO\Directory;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

Class articul_main extends \CModule
{
    public $MODULE_ID;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS;
    public $PARTNER_NAME;
    public $PARTNER_URI;
    
    public function __construct()
    {
        $arModuleVersion = [];
        include(dirname(__FILE__) . "/version.php");
        
        $this->MODULE_NAME = "Articul.Main";
        $this->MODULE_DESCRIPTION = "Главный модуль для orm моделей и тд.";
        $this->MODULE_ID = 'articul.main';
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = 'Articul';
        $this->PARTNER_URI = 'soon';
    }
    
    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
    }
    
    public function DoUninstall()
    {
        ModuleManager::unregisterModule($this->MODULE_ID);
        
        return true;
    }
}
