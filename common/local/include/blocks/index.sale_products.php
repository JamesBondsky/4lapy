<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

use FourPaws\Catalog\Model\Offer;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @todo региональность (PRICE_...)
 *
 * Блок на главной странице: распродажа
 */

/** @global $APPLICATION */
$APPLICATION->IncludeComponent('fourpaws:catalog.snippet.list', '', [
    'COUNT'        => 12,
    'OFFER_FILTER' => [
        [
            'LOGIC' => 'OR',
            [
                '=PROPERTY_COND_FOR_ACTION' => Offer::SIMPLE_SHARE_DISCOUNT_CODE,
                '>PROPERTY_COND_VALUE' => 0
            ],
            [
                '=PROPERTY_COND_FOR_ACTION' => Offer::SIMPLE_SHARE_SALE_CODE,
                '>PROPERTY_PRICE_ACTION' => 0
            ]
        ],
        '>CATALOG_PRICE_2' => 0,
    ],
    'TITLE'        => 'Распродажа',
], false, ['HIDE_ICONS' => 'Y']);
