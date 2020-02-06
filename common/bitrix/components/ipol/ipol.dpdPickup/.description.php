<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("IPOLH_DPD_COMP_NAME"),
	"DESCRIPTION" => GetMessage("IPOLH_DPD_COMP_DESCR"),
	"ICON" => "/images/icon.png",
	"CACHE_PATH" => "Y",
	"SORT" => 40,
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "ipol",
			"NAME" => GetMessage("IPOL_DPD_GROUP"),
			"SORT" => 30,
			"CHILD" => array(
				"ID" => "ipol_dpdPickup",
			),
		),
	),
);
?>