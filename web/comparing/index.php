<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Сравнение");
$APPLICATION->IncludeComponent(
    "articul:comparing",
    "",
    [
        'SEF_FOLDER' => '/comparing/'
    ]
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>