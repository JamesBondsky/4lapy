<?php
/**
 * @var CMain   $APPLICATION
 * @var array   $arParams
 * @var array   $arResult
 * @var Product $product
 * @var Offer   $offer
 */

use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

//global $APPLICATION;
$product = $APPLICATION->IncludeComponent(
    'fourpaws:catalog.element.detail',
    'catalog',
    [
        'CODE' => $arResult['VARIABLES']['ELEMENT_ID'],
    ]
);