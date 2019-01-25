<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Сравнение");
$APPLICATION->IncludeComponent(
    "articul:comparing.import",
    "",
    []
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>