<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Блок "Популярные товары"
 *
 * @updated: 18.01.2018
 */

/** @global $APPLICATION */
/** @var array $arParams */

$APPLICATION->IncludeComponent(
    'fourpaws:catalog.products.recommendations',
    'fp.17.0.popular',
    [
        'DEFERRED_LOAD' => 'Y',
        'RCM_TYPE' => 'personal',
        'USE_BIG_DATA' => 'Y',
        'USE_BESTSELLERS' => 'Y',
        'USE_MOST_VIEWED' => 'Y',
        'USE_RANDOM' => 'Y',
        'USE_SAME_PURCHASE' => 'N',
        'PAGE_ELEMENT_COUNT' => 10,
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);
