<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\StoreBundle\Entity\Store;

/**
 * @var array $arResult
 * @var array $arParams
 */

if (empty($arResult['STOCK_RESULT_BY_SHOP'])) {
    return;
}

/**
 * 1) По убыванию % от суммы товаров заказа в наличии в магазине или на складе
 * 2) По возрастанию даты готовности заказа к выдаче
 * 3) По адресу магазина в алфавитном порядке
 */

/** @var array $stockResultByShop */
$stockResultByShop = $arResult['STOCK_RESULT_BY_SHOP'];

$sortFunc = function ($shop1, $shop2) use ($stockResultByShop) {
    /** @var Store $shop1 */
    /** @var Store $shop2 */
    $shopData1 = $stockResultByShop[$shop1->getXmlId()];
    $shopData2 = $stockResultByShop[$shop2->getXmlId()];

    if ($shopData1['AVAILABLE_AMOUNT'] != $shopData2['AVAILABLE_AMOUNT']) {
        return ($shopData1['AVAILABLE_AMOUNT'] > $shopData2['AVAILABLE_AMOUNT']) ? -1 : 1;
    }

    /** @var StockResultCollection $stockResult1 */
    $stockResult1 = $shopData1['STOCK_RESULT'];
    /** @var StockResultCollection $stockResult2 */
    $stockResult2 = $shopData2['STOCK_RESULT'];
    $deliveryDate1 = $stockResult1->getDeliveryDate();
    $deliveryDate2 = $stockResult2->getDeliveryDate();

    if ($deliveryDate1 != $deliveryDate2) {
        return ($shopData1['AVAILABLE_AMOUNT'] > $shopData2['AVAILABLE_AMOUNT']) ? 1 : -1;
    }

    return $shop1->getAddress() > $shop2->getAddress() ? 1 : -1;
};

uasort($arResult['SHOPS'], $sortFunc);
uasort($arResult['SHOPS_FULL'], $sortFunc);
uasort($arResult['SHOPS_PARTIAL'], $sortFunc);
