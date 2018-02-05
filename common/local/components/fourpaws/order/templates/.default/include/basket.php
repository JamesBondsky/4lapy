<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var Basket $basket
 * @var CalculationResult $selectedDelivery
 */

use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Main\Grid\Declension;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\Helpers\WordHelper;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderService;

$basket = $arResult['BASKET'];

/* @todo отображение акционных товаров */

/** @var OrderStorage $storage */
$storage = $arResult['STORAGE'];

/** @var DeliveryService $deliveryService */
$deliveryService = Application::getInstance()->getContainer()->get('delivery.service');

$selectedShop = $arResult['SELECTED_SHOP'];
$showDelayed = ($arResult['STEP'] === OrderService::DELIVERY_STEP)
    && $storage->isPartialGet()
    && ($selectedShop instanceof Store)
    && $deliveryService->isPickup($selectedDelivery);

if ($showDelayed) {
    $stockResult = $deliveryService->getStockResultByDelivery($selectedDelivery)->filterByStore($selectedShop);

    $available = $stockResult->getAvailable();
    $availableWeight = 0;
    $availablePrice = 0;
    $availableQuantity = $available->getAmount();
    $availableItems = [];
    /** @var StockResult $item */
    foreach ($available as $item) {
        $availableItems[] = [
            'name'     => $item->getOffer()->getName(),
            'quantity' => $item->getAmount(),
            'price'    => $item->getPrice(),
        ];

        $availablePrice += $item->getPrice() * $item->getAmount();
        $availableWeight += $item->getOffer()->getCatalogProduct()->getWeight() * $item->getAmount();
    }

    $delayed = $stockResult->getDelayed();
    $delayedWeight = 0;
    $delayedPrice = 0;
    $delayedQuantity = $delayed->getAmount();
    $delayedItems = [];
    /** @var StockResult $item */
    foreach ($delayed as $item) {
        $delayedItems[] = [
            'name'     => $item->getOffer()->getName(),
            'quantity' => $item->getAmount(),
            'price'    => $item->getPrice(),
        ];

        $delayedPrice += $item->getPrice() * $item->getAmount();
        $delayedWeight += $item->getOffer()->getCatalogProduct()->getWeight() * $item->getAmount();
    }
} else {
    $availableQuantity = array_sum($basket->getQuantityList());
    $availableWeight = $basket->getWeight();
    $availablePrice = $basket->getPrice();
    $availableItems = [];
    /* @var BasketItem $item */
    foreach ($basket as $item) {
        $availableItems[] = [
            'name'     => $item->getField('NAME'),
            'quantity' => $item->getQuantity(),
            'price'    => $item->getPrice(),
        ];
    }
}

?>

<aside class="b-order__list">
    <h4 class="b-title b-title--order-list js-popup-mobile-link">
        Заказ: <?= $availableQuantity ?> <?= (new Declension('товар', 'товара', 'товаров'))->get(
            $availableQuantity
        ) ?>
        (<?= WordHelper::showWeight($availableWeight) ?>) на сумму <?= CurrencyHelper::formatPrice(
            $availablePrice
        ) ?>
    </h4>
    <div class="b-order-list js-popup-mobile">
        <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о
            заказе</a>
        <ul class="b-order-list__list js-order-list-block">
            <?php foreach ($availableItems as $item) { ?>
                <li class="b-order-list__item">
                    <div class="b-order-list__order-text">
                        <div class="b-order-list__clipped-text">
                            <div class="b-order-list__text-backed">
                                <?= $item['name'] ?>
                                <?php if ($item['quantity'] > 1) { ?>
                                    (<?= $item['quantity'] ?> шт)
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value">
                        <?= CurrencyHelper::formatPrice($item['price'] * $item['quantity']) ?>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
    <?php if ($showDelayed) { ?>
        <h4 class="b-title b-title--order-list js-popup-mobile-link js-basket-link">
            <span class="js-mobile-title-order">Останется в корзине: <?= $delayedQuantity ?></span>
            <?= (new Declension('товар', 'товара', 'товаров'))->get(
                $delayedQuantity
            ) ?> (<?= WordHelper::showWeight($delayedWeight) ?>) на сумму <?= CurrencyHelper::formatPrice(
                $delayedPrice
            ) ?>
        </h4>
        <div class="b-order-list b-order-list--aside js-popup-mobile">
            <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о заказе</a>
            <ul class="b-order-list__list js-order-list-block">
                <?php foreach ($delayedItems as $item) { ?>
                    <li class="b-order-list__item b-order-list__item--aside">
                        <div class="b-order-list__order-text b-order-list__order-text--aside">
                            <div class="b-order-list__clipped-text">
                                <div class="b-order-list__text-backed">
                                    <?= $item['name'] ?>
                                    <?php if ($item['quantity'] > 1) { ?>
                                        (<?= $item['quantity'] ?> шт)
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="b-order-list__order-value b-order-list__order-value--aside">
                            <?= CurrencyHelper::formatPrice($item['price'] * $item['quantity']) ?>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <div class="b-order__link-wrapper">
            <a class="b-link b-link--order-gotobusket b-link--order-gotobusket"
               href="javascript:void(0)"
               title="Вернуться в корзину">
                <span class="b-icon b-icon--order-busket">
                    <?= new SvgDecorator('icon-reason', 16, 16) ?>
                </span>
                <span class="b-link__text b-link__text--order-gotobusket">Вернуться в корзину</span>
            </a>
        </div>
    <?php } ?>
</aside>
