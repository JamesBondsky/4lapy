<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Блок "Выгодная покупка"
 *
 * @updated: 18.01.2018
 */

/** @global $APPLICATION */
/** @var array $arParams */

$APPLICATION->IncludeComponent(
    'fourpaws:catalog.products.recommendations',
    'fp.17.0.followup',
    [
        'DEFERRED_LOAD' => 'Y',// Y - отложенная загрузка

        'RCM_TYPE' => 'postcross',
        'POSTCROSS_IDS' => isset($arParams['POSTCROSS_IDS']) ? $arParams['POSTCROSS_IDS'] : [],
        'USE_BIG_DATA' => 'Y',
        'USE_BESTSELLERS' => 'N',
        'USE_MOST_VIEWED' => 'N',
        'USE_RANDOM' => 'N',
        'USE_SAME_PURCHASE' => 'N',
        'PAGE_ELEMENT_COUNT' => 10,

        'WRAP_CONTAINER_BLOCK' => isset($arParams['WRAP_CONTAINER_BLOCK']) ? $arParams['WRAP_CONTAINER_BLOCK'] : 'Y',
        'SHOW_TOP_LINE' => isset($arParams['SHOW_TOP_LINE']) ? $arParams['SHOW_TOP_LINE'] : 'N',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);
