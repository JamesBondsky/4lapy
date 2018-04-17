<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var Basket $basket
 * @var CalculationResultInterface $selectedDelivery
 */

use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Main\Grid\Declension;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\Helpers\WordHelper;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\SaleBundle\Entity\OrderStorage;

/** @var OrderStorage $storage */
$storage = $arResult['STORAGE'];

/** @noinspection PhpUnhandledExceptionInspection */
/** @var DeliveryService $deliveryService */
$deliveryService = Application::getInstance()->getContainer()->get('delivery.service');

$showPickupContainer = false;
$showDelayedItems = false;

$availableItems = [];
$availableWeight = 0;
$availablePrice = 0;
$availableQuantity = 0;
$delayedItems = [];
$delayedWeight = 0;
$delayedPrice = 0;
$delayedQuantity = 0;
$selectedDelivery = $arResult['SELECTED_DELIVERY'];

if ($deliveryService->isPickup($selectedDelivery)) {
    $showPickupContainer = true;
}

/** @var PickupResultInterface $pickup */
$pickup = $arResult['PICKUP'];
/** @var CalculationResultInterface $delivery */
$delivery = $arResult['DELIVERY'];

if (null !== $pickup) {
    /** @var Store $selectedShop */
    $selectedShop = $arResult['SELECTED_SHOP'];
    $stockResult = $pickup->getStockResult();

    $available = $stockResult->getAvailable();
    $availableWeight = 0;
    $availablePrice = 0;
    // если нет доступных товаров, частичного получения не будет
    if ($available->isEmpty()) {
        $available = $stockResult->getDelayed();
        $availableQuantity = $available->getAmount();
        foreach ($available as $item) {
            $availableItems[] = [
                'name' => $item->getOffer()->getName(),
                'quantity' => $item->getAmount(),
                'price' => $item->getPrice(),
            ];

            $availablePrice += $item->getPrice() * $item->getAmount();
            $availableWeight += $item->getOffer()->getCatalogProduct()->getWeight() * $item->getAmount();
        }
    } else {
        $availableQuantity = $available->getAmount();
        $availableItems = [];
        /** @var StockResult $item */
        foreach ($available as $item) {
            $availableItems[] = [
                'name' => $item->getOffer()->getName(),
                'quantity' => $item->getAmount(),
                'price' => $item->getPrice(),
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
                'name' => $item->getOffer()->getName(),
                'quantity' => $item->getAmount(),
                'price' => $item->getPrice(),
            ];

            $delayedPrice += $item->getPrice() * $item->getAmount();
            $delayedWeight += $item->getOffer()->getCatalogProduct()->getWeight() * $item->getAmount();
        }

        if (!$delayed->isEmpty()) {
            $showDelayedItems = true;
        }
    }
}
if (null !== $delivery) {
    $deliveryResult = $delivery->getStockResult();
    $deliveryOrderableResult = $deliveryResult->getOrderable();
    $deliveryOrderableQuantity = $deliveryOrderableResult->getAmount();
    $deliveryOrderablePrice = $deliveryOrderableResult->getPrice();
    $deliveryOrderableWeight = 0;
    $deliveryOrderableItems = [];
    foreach ($deliveryOrderableResult->getIterator() as $item) {
        $offer = $item->getOffer();
        $offerId = $item->getOffer()->getId();
        $deliveryOrderableItems[$offerId]['quantity'] += $item->getAmount();
        $deliveryOrderableItems[$offerId]['name'] = $offer->getName();
        $deliveryOrderableItems[$offerId]['weight'] += $offer->getCatalogProduct()->getWeight() * $item->getAmount();
        $deliveryOrderableItems[$offerId]['price'] += $item->getPrice() * $item->getAmount();
        $deliveryOrderableWeight += $offer->getCatalogProduct()->getWeight() * $item->getAmount();
    }

    $deliveryUnavailableResult = $deliveryResult->getUnavailable();
    $deliveryUnavailableQuantity = $deliveryUnavailableResult->getAmount();
    $deliveryUnavailablePrice = $deliveryUnavailableResult->getPrice(false);
    $deliveryUnavailableWeight = 0;
    $deliveryUnavailableItems = [];
    foreach ($deliveryUnavailableResult->getIterator() as $item) {
        $offer = $item->getOffer();
        $offerId = $item->getOffer()->getId();
        $deliveryUnavailableItems[$offerId]['quantity'] += $item->getAmount();
        $deliveryUnavailableItems[$offerId]['name'] = $offer->getName();
        $deliveryUnavailableItems[$offerId]['weight'] += $offer->getCatalogProduct()->getWeight() * $item->getAmount();
        $deliveryUnavailableItems[$offerId]['price'] += $item->getPrice() * $item->getAmount();
        $deliveryUnavailableWeight += $offer->getCatalogProduct()->getWeight() * $item->getAmount();
    }
    ?>
    <?php /* отображается на 2 шаге, когда выбрана курьерская доставка */ ?>
    <aside class="b-order__list js-list-orders-static" <?= !$showPickupContainer ? '' : 'style="display:none"' ?>>
        <h4 class="b-title b-title--order-list js-popup-mobile-link js-full-list-title">
        <span class="js-mobile-title-order">Заказ: <?= $deliveryOrderableQuantity ?> <?= (new Declension(
                'товар',
                'товара',
                'товаров'
            ))->get(
                $deliveryOrderableQuantity
            ) ?>
            (<?= WordHelper::showWeight($deliveryOrderableWeight, true) ?>) на сумму <?= CurrencyHelper::formatPrice(
                $deliveryOrderablePrice,
                false
            ) ?>
        </h4>
        <div class="b-order-list b-order-list--aside js-full-list js-popup-mobile">
            <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о заказе</a>
            <ul class="b-order-list__list js-order-list-block">
                <?php foreach ($deliveryOrderableItems as $item) { ?>
                    <li class="b-order-list__item b-order-list__item--aside js-full-list">
                        <div class="b-order-list__order-text b-order-list__order-text--aside js-full-list">
                            <div class="b-order-list__clipped-text">
                                <div class="b-order-list__text-backed">
                                    <?= $item['name'] ?>
                                    <?php if ($item['quantity'] > 1) { ?>
                                        (<?= $item['quantity'] ?> шт)
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="b-order-list__order-value b-order-list__order-value--aside js-full-list">
                            <?= CurrencyHelper::formatPrice($item['price']) ?>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <h4 class="b-title b-title--order-list js-parts-list-title"
            <?= $deliveryUnavailableResult->isEmpty() ? 'style="display:none"' : '' ?>>
            <span class="js-mobile-title-order">Останется в корзине: <?= $deliveryUnavailableQuantity ?></span>
            <?= (new Declension('товар', 'товара', 'товаров'))->get(
                $deliveryUnavailableQuantity
            ) ?> (<?= WordHelper::showWeight($deliveryUnavailableWeight, true) ?>) на сумму <?= CurrencyHelper::formatPrice(
                $deliveryUnavailablePrice,
                false
            ) ?>
        </h4>
        <div class="b-order-list b-order-list--aside js-popup-mobile js-parts-list"
            <?= $deliveryUnavailableResult->isEmpty() ? 'style="display:none"' : '' ?>>
            <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о
                заказе</a>
            <ul class="b-order-list__list js-order-list-block">
                <?php foreach ($deliveryUnavailableItems as $item) { ?>
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
                            <?= CurrencyHelper::formatPrice($item['price']) ?>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </aside>
<?php } ?>
<?php /* отображается на 2 шаге, когда выбран самовывоз */ ?>
<aside class="b-order__list js-list-orders-cont" <?= $showPickupContainer ? '' : 'style="display:none"' ?>>
    <h4 class="b-title b-title--order-list js-popup-mobile-link js-full-list-title">
        Заказ: <?= $availableQuantity ?> <?= (new Declension('товар', 'товара', 'товаров'))->get(
            $availableQuantity
        ) ?>
        (<?= WordHelper::showWeight($availableWeight, true) ?>) на сумму <?= CurrencyHelper::formatPrice(
            $availablePrice,
            false
        ) ?>
    </h4>
    <div class="b-order-list js-popup-mobile js-full-list">
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
    <h4 class="b-title b-title--order-list js-parts-list-title"
        <?= !$showDelayedItems ? 'style="display:none"' : '' ?>>
        <span class="js-mobile-title-order">Останется в корзине: <?= $delayedQuantity ?></span>
        <?= (new Declension('товар', 'товара', 'товаров'))->get(
            $delayedQuantity
        ) ?> (<?= WordHelper::showWeight($delayedWeight, true) ?>) на сумму <?= CurrencyHelper::formatPrice(
            $delayedPrice,
            false
        ) ?>
    </h4>
    <div class="b-order-list b-order-list--aside js-popup-mobile js-parts-list"
        <?= !$showDelayedItems ? 'style="display:none"' : '' ?>>
        <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о
            заказе</a>
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
    <div class="b-order__link-wrapper"
        <?= !$showDelayedItems ? 'style="display:none"' : '' ?>>
        <a class="b-link b-link--order-gotobusket b-link--order-gotobusket"
           href="/cart"
           title="Вернуться в корзину">
            <span class="b-icon b-icon--order-busket">
                <?= /** @noinspection PhpUnhandledExceptionInspection */
                new SvgDecorator('icon-reason', 16, 16) ?>
            </span>
            <span class="b-link__text b-link__text--order-gotobusket">Вернуться в корзину</span>
        </a>
    </div>
</aside>
