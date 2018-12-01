<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
    "NAME" => GetMessage('BXMOD_AUTH_COMPONENT_NAME'),
    "DESCRIPTION" => GetMessage('BXMOD_AUTH_COMPONENT_DESC'),
    "ICON" => "/images/catalog.gif",
    "SORT" => 10,
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => "service",
        "CHILD" => array(
            "ID" => "bxmodAuth",
            "NAME" => GetMessage('BXMOD_AUTH_COMPONENT_GROUP_NAME'),
            "SORT" => 10
        ),
    ),
);

?>