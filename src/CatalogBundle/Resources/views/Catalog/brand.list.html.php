<?php
require $_SERVER['DOCUMENT_ROOT']. '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Все бренды производителей товаров для животных представлены в интернет-магазине Четыре Лапы');
$APPLICATION->SetPageProperty('description', '*Убрать первые "Бренды"');
$APPLICATION->SetTitle("Все бренды производителей товаров для животных представлены в интернет-магазине Четыре Лапы");

$APPLICATION->IncludeComponent(
    'fourpaws:brands',
    'fp.17.0',
    array(
        'CACHE_TYPE' => 'A',
        'CACHE_TIME' => '43200',
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/brand/',
        'SEF_URL_TEMPLATES' => array(
            'index' => 'index.php',
            //'letter' => '#LETTER_REDUCED#/'
        ),
    ),
    null,
    array(
        'HIDE_ICONS' => 'Y'
    )
);

require $_SERVER['DOCUMENT_ROOT']. '/bitrix/footer.php';
die();
