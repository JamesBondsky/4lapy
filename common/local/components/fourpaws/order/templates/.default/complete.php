<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var CMain $APPLICATION
 * @var array $arParams
 */
$template = '';
if(new DateTime() <= new DateTime('2019-09-30 23:59:59')){
    $template = 'dobrolap';
}


$APPLICATION->IncludeComponent(
    'fourpaws:order.complete',
    $template,
    [
        'ORDER_ID'  => $arParams['ORDER_ID'],
        'HASH'      => $arParams['HASH'],
        'SET_TITLE' => $arParams['SET_TITLE'],
    ],
    false,
    ['HIDE_ICONS' => 'Y']
);