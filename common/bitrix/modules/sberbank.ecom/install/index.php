<?
use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class sberbank_ecom extends CModule
{

    var $MODULE_ID = 'sberbank.ecom';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;

    protected $langMess;

    function __construct() {


        $path = realpath(dirname(dirname(__FILE__)));
        require($path . "/config.php");

        $arModuleVersion = array();
        include __DIR__ . '/version.php';

        $this->MODULE_NAME = GetMessage('RBS_MODULE_NAME') . " - " . RBS_BANK_NAME;
        $this->MODULE_DESCRIPTION = GetMessage('RBS_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = GetMessage('RBS_PARTNER_NAME');
        $this->PARTNER_URI = GetMessage('RBS_PARTNER_URI');

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
    }


    function DoInstall()
    {
        $this->InstallFiles();
        RegisterModule($this->MODULE_ID);
        COption::SetOptionInt($this->MODULE_ID, "delete", false);
    }


    function InstallFiles($arParams = array())
    {

        $path = realpath(dirname(dirname(__FILE__))) . "/install/sale_payment/payment/";
        $files = new DirectoryIterator($path);

        foreach ($files as $file) {
            // excluding the . and ..
            if ($file->isDot() === false) {
                // seek and replace ;)
                $path_to_file = $file->getPathname();
                $file_contents = file_get_contents($path_to_file);
                $file_contents = str_replace("{module_path}", $this->MODULE_ID, $file_contents);
                file_put_contents($path_to_file, $file_contents);
            }
        }

        CopyDirFiles(
            realpath(dirname(dirname(__FILE__))) . "/install/sale_payment/payment/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/include/sale_payment/payment/"
        );

        CopyDirFiles(
            realpath(dirname(dirname(__FILE__))) . "/install/sale/payment/",
            $_SERVER["DOCUMENT_ROOT"] . "/sale/payment/"
        );

        CopyDirFiles(
            realpath(dirname(dirname(__FILE__))) . "/ajax.php",
            $_SERVER['DOCUMENT_ROOT'] . "/" . $this->MODULE_ID . "/ajax.php"
        );
    }


    function DoUninstall()
    {
        COption::SetOptionInt($this->MODULE_ID, "delete", true);
        UnRegisterModule($this->MODULE_ID);
        $this->UnInstallFiles();
    }


    function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/php_interface/include/sale_payment/payment");
        DeleteDirFilesEx("/sale/payment/");
        DeleteDirFilesEx($this->MODULE_ID);
    }
}