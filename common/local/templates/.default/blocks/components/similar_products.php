<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Блок "Похожие товары"
 *
 * @updated: 18.01.2018
 */

/** @global $APPLICATION */
/** @var array $arParams */

$APPLICATION->IncludeComponent(
    'fourpaws:catalog.products.recommendations',
    'fp.17.0.similar',
    [
        'DEFERRED_LOAD' => 'N',// Y - отложенная загрузка

        'RCM_TYPE' => 'similar',
        'RCM_PROD_ID' => isset($arParams['PRODUCT_ID']) ? $arParams['PRODUCT_ID'] : 0,
        'USE_BIG_DATA' => 'Y',
        'USE_BESTSELLERS' => 'N',
        'USE_MOST_VIEWED' => 'N',
        'USE_RANDOM' => 'N',
        'USE_SAME_PURCHASE' => 'N',
        'PAGE_ELEMENT_COUNT' => 10,
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);
