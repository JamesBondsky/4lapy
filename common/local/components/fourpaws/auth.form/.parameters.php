<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = array(
    "PARAMETERS" => array(
        "BACK_URL_HASH" => Array(
            "NAME"=>GetMessage("BACK_URL_HASH_NAME"),
            "TYPE" => "TEXT",
            "DEFAULT"=>'',
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
    )
);