<?php
// admin initialization
define("ADMIN_MODULE_NAME", "alexkova.fileinspector");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/alexkova.fileinspector/lang/".LANGUAGE_ID."/classes/general/checkfile.php");

IncludeModuleLangFile(__FILE__);
if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

if (!CModule::IncludeModule(ADMIN_MODULE_NAME))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$APPLICATION->SetTitle(GetMessage('FILEINSPECTOR_ADMIN_STAT_TITLE'));

//require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");
?>

<?


$stat = CAFIStat::getStat();
$deffectCodes =  GetMessage('DEFFECT_CODE');
$deffectCodes[0] = GetMessage('NO_DEFFECT');

if(!is_array($stat["ITEMS"])) $stat["ITEMS"] = array();

foreach ($stat["ITEMS"] as $cell=>$val){

	if (intval($val["DEFFECT_CODE"]) == 0){
		$stat["ITEMS"][$cell]["DEFFECT_CODE"] = "OK";
	}

	$stat["ITEMS"][$cell]["NAME"] = $deffectCodes[intval($val["DEFFECT_CODE"])];
	$stat["ITEMS"][$cell]["SIZE"] = CAFITools::FBytes($stat["ITEMS"][$cell]["SIZE"], 2);
}

$sTableID = 'stat_table';
$oSort = new CAdminSorting($sTableID, "DEFFECT_CODE", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arHeaders = array();

$arHeaders[] = array(
	'id' => "DEFFECT_CODE",
	'content' => GetMessage('FILEINSPECTOR_FIELD_ERROR_CODE'),
	'sort' => false,
	'default' => true,
);

$arHeaders[] = array(
	'id' => "CNT",
	'content' => GetMessage('FILEINSPECTOR_FIELD_CHECKED'),
	'sort' => false,
	'default' => true,
);

$arHeaders[] = array(
	'id' => "SIZE",
	'content' => GetMessage('FILEINSPECTOR_FIELD_SIZE'),
	'sort' => false,
	'default' => true,
);

$arHeaders[] = array(
	'id' => "NAME",
	'content' => GetMessage('FILEINSPECTOR_FIELD_ERROR_DESCRIPTION'),
	'sort' => false,
	'default' => true,
);

$lAdmin->AddHeaders($arHeaders);

$aMenu = array();

$context = new CAdminContextMenu($aMenu);

$lAdmin->AddAdminContextMenu(array());


$lAdmin->CheckListMode();

foreach ($stat["ITEMS"] as $cell=>$item){

	$row = $lAdmin->AddRow($item['DEFFECT_CODE'], $item);
}


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();


CAFISearcher::showDocNotes();


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>