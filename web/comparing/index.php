<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Сравнение");
$APPLICATION->IncludeComponent(
	"articul:comparing", 
	".default", 
	array(
		"SEF_FOLDER" => "/comparing/",
		"TEXT_HEADER" => "Сравнение кормов",
		"TEXT_SELECT_BRAND" => "Выберите бренд",
		"TEXT_SELECT_PRODUCT" => "Выберите корм",
		"TEXT_BUTTON" => "Сравнить",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>