<?php

/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';


global $APPLICATION;
$APPLICATION->SetTitle('Импорт/экспорт для раздела "Сравнение"');

/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
\CUtil::InitJSCore(["jquery"]);

$APPLICATION->IncludeComponent(
    "articul:comparing.import",
    "",
    [
            'TYPE' => $_REQUEST['type']
    ]
);

/** @noinspection PhpIncludeInspection */
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
