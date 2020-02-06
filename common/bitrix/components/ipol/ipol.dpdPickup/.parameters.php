<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!\Bitrix\Main\Loader::includeModule("sale"))
	return;

if(!cmodule::includeModule('ipol.dpd'))
	return false;

$arComponentParameters = array(
	"PARAMETERS" => array(
		"USER_LOCATION" => array(
			"PARENT"   => "BASE",
			"NAME"     => GetMessage("IPOL_DPD_COMPOPT_USER_LOCATION"),
			"TYPE"     => "STRING",
		),

		"SHOP_LOCATION" => array(
			"PARENT"   => "BASE",
			"NAME"     => GetMessage("IPOL_DPD_COMPOPT_SHOP_LOCATION"),
			"TYPE"     => "STRING",
		),
	),
);
?>