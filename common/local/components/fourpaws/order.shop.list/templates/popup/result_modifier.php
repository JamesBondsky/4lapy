<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
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

$sortFunc = function (Store $shop1, Store $shop2) use ($stockResultByShop) {
    $shopData1 = $stockResultByShop[$shop1->getXmlId()];
    $shopData2 = $stockResultByShop[$shop2->getXmlId()];

    if ($shopData1['AVAILABLE_AMOUNT'] !== $shopData2['AVAILABLE_AMOUNT']) {
        return ($shopData1['AVAILABLE_AMOUNT'] > $shopData2['AVAILABLE_AMOUNT']) ? -1 : 1;
    }

    /** @var BaseResult $result1 */
    $result1 = $shopData1['FULL_RESULT'];
    /** @var BaseResult $result2 */
    $result2 = $shopData2['FULL_RESULT'];
    $deliveryDate1 = $result1->getDeliveryDate();
    $deliveryDate2 = $result2->getDeliveryDate();

    if ($deliveryDate1->getTimestamp() !== $deliveryDate2->getTimestamp()) {
        return ($deliveryDate1->getTimestamp() > $deliveryDate2->getTimestamp()) ? 1 : -1;
    }

    return $shop1->getAddress() > $shop2->getAddress() ? 1 : -1;
};

uasort($arResult['SHOPS'], $sortFunc);
uasort($arResult['SHOPS_FULL'], $sortFunc);
uasort($arResult['SHOPS_PARTIAL'], $sortFunc);
