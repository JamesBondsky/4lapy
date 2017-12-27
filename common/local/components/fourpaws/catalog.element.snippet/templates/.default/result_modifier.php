<?php
/**
 * @var array            $arParams
 * @var array            $arResult
 * @var Product          $product
 * @var Offer            $offer
 * @var Offer            $firstOffer
 * @var CBitrixComponent $this
 */

use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\OfferQuery;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @todo Выпилить. Временный хотфикс
 */

$product = $arResult['PRODUCT'];

$arResult['OFFERS'] = (new OfferQuery())
    ->withFilterParameter('=PROPERTY_CML2_LINK', $product->getId())
    ->exec()
    ->toArray();

$this->setResultCacheKeys(['OFFERS']);
