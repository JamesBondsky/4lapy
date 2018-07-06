<?php

use FourPaws\Catalog\Model\Offer;
use FourPaws\Components\BasketComponent;

/** @global BasketComponent $component */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if ($arParams['IS_AJAX']) {
    return;
}

/** @var Bitrix\Sale\Basket\ $basket */
$basket = $arResult['BASKET']->getOrderableItems();
$userDiscount = $component->getCurrentUserService()->getDiscount();
$offers = $templateData['OFFERS'];
$bonus = [];
if (\is_array($offers) && !empty($offers)) {
    /** @var \Bitrix\Sale\BasketItem $basketItem */
    $bonusAwardingQuantity = [];
    foreach ($basket as $basketItem) {
        $bonusAwardingQuantity[$basketItem->getProductId()] += $basketItem->getPropertyCollection()->getPropertyValues()['HAS_BONUS']['VALUE'];
    }

    $bonusAwardingQuantity = \array_filter($bonusAwardingQuantity);
    foreach ($bonusAwardingQuantity as $productId => $quantity) {
        /** @var Offer $offer */
        $offer = $component->getOffer($productId);

        $bonus[$productId] = $offer->getBonusFormattedText(
            $userDiscount,
            $quantity,
            0
        );
    }
}

if ($bonus) {
    ?>
    <script>
        $(document).ready(function() {
            let bonus = <?= CUtil::PhpToJSObject($bonus) ?>;
            for (let productId in bonus) {
                $('.js-bonus-' + productId).text(bonus[productId]);
            }
        })
    </script>
    <?php
}
