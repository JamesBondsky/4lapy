<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var CMain $APPLICATION
 * @var array $arParams
 */

$APPLICATION->IncludeComponent(
    'fourpaws:order.complete',
    '',
    [
        'ORDER_ID'  => $arParams['ORDER_ID'],
        'HASH'      => $arParams['HASH'],
        'SET_TITLE' => $arParams['SET_TITLE'],
    ],
    false,
    ['HIDE_ICONS' => 'Y']
);
