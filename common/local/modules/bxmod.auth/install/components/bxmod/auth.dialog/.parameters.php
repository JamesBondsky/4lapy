<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentParameters = Array (
    "GROUPS" => array(
        "GENERAL" => array(
            "NAME" => GetMessage('BXMOD_AUTH_GENERAL_SETTINGS'),
        )
    ),
    "PARAMETERS" => array(
        "SUCCESS_RELOAD_TIME" => Array(
            "PARENT" => "GENERAL",
            "NAME" => GetMessage('BXMOD_AUTH_REFRESH_TIMEOUT'),
            "TYPE" => "STRING",
            "DEFAULT" => '5',
            "SORT" => 20
        )
    )
);
?>