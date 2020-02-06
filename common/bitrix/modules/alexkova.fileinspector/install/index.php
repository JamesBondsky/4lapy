<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-18);
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));
//echo "<pre>"; print_r(GetLangFileName($strPath2Lang."/lang/", "/install/index.php")); echo "</pre>";


Class alexkova_fileinspector extends CModule
{
	var $MODULE_ID = "alexkova.fileinspector";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

	function alexkova_fileinspector()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			//$this->MODULE_VERSION = COMPRESSION_VERSION;
			//$this->MODULE_VERSION_DATE = COMPRESSION_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("alexkova.fileinspector_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("alexkova.fileinspector_MODULE_DESC");
		$this->PARTNER_NAME = GetMessage("alexkova.fileinspector_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("alexkova.fileinspector_PARTNER_URI");
	}
	

	function InstallDB($arParams = array())
	{
		global $DB;

		//echo(dirname(__FILE__).'/mysql/install.sql'); die();

		$DB->RunSQLBatch(dirname(__FILE__).'/mysql/install.sql');

		RegisterModule($this->MODULE_ID);

                
                //RegisterModuleDependences("main", "OnAdminListDisplay", "alexkova.dirsizer", "CAlexKovaDirsizer", "DirsizerOnAdminListDisplay");
                
                return true;
	}

	function UnInstallDB($arParams = array())
	{
		//UnRegisterModuleDependences("main", "OnAdminListDisplay", "alexkova.dirsizer", "CAlexKovaDirsizer", "DirsizerOnAdminListDisplay");

		if ($arParams["savedata"] != "Y"){

			global $DB;
			$DB->RunSQLBatch(dirname(__FILE__).'/mysql/uninstall.sql');

		}

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

	function InstallFiles($arParams = array())
	{
		    if (is_dir($p = dirname(__FILE__).'/js'))
            {

                    if ($dir = opendir($p))
                    {

                            while (false !== $item = readdir($dir))
                            {
                                    if ($item == '..' || $item == '.')
                                            continue;
                                    CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.$item, $ReWrite = True, $Recursive = True);
                            }
                            closedir($dir);
                    }
            }
            
            if (is_dir($p = dirname(__FILE__).'/admin'))
            {
				//print_r($p); die();
                    if ($dir = opendir($p))
                    {
                            while (false !== $item = readdir($dir))
                            {
                                    if ($item == '..' || $item == '.')
                                            continue;
                                    CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$item, $ReWrite = True, $Recursive = True);
                            }
                            closedir($dir);
                    }
            }

            
		return true;
	}

	function UnInstallFiles()
	{
                
		return true;
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
                //$GLOBALS["errors"] = $this->errors;
		$APPLICATION->IncludeAdminFile(GetMessage("FILEINSPECTOR_MODULE_INSTALL"), dirname(__FILE__)."/step.php");
	}

	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
                $savedata = $_REQUEST["savedata"];
                $step = $_REQUEST["step"];
                $STAT_RIGHT = $APPLICATION->GetGroupRight($this->MODULE_ID);
		
		{
			$step = IntVal($step);
                        if ($step<2)
                        {
                            $APPLICATION->IncludeAdminFile(GetMessage("FILEINSPECTOR_UNINSTALL_TITLE"), dirname(__FILE__)."/unstep1.php");
                        }
                        elseif($step == 2)
                        {
                            $this->UnInstallDB(array("savedata"=>$savedata));
                            $GLOBALS["errors"] = $this->errors;
                            $APPLICATION->IncludeAdminFile(GetMessage("FILEINSPECTOR_UNINSTALL_TITLE"), dirname(__FILE__)."/unstep2.php");
                        }
                }        
                
                
                
	}
}
?>