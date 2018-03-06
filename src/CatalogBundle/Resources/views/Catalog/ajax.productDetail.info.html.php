<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

/** @noinspection PhpIncludeInspection */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use  FourPaws\Catalog\Model\Offer;

/**
 * @var CMain $APPLICATION
 * @var Offer $offer
 */

$APPLICATION->IncludeComponent(
    'fourpaws:catalog.product.delivery.info',
    'detail',
    [
        'OFFER' => $offer
    ],
    false,
    ['HIDE_ICONS' => 'Y']
);
