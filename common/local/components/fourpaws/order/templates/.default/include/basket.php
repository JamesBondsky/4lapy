<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array                      $arParams
 * @var array                      $arResult
 * @var Basket                     $basket
 * @var CalculationResultInterface $selectedDelivery
 * @var FourPawsOrderComponent     $component
 */

use Bitrix\Sale\Basket;
use Bitrix\Main\Grid\Declension;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;
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
/** @var CalculationResultInterface $deliveryDobrolap */
$deliveryDobrolap = $arResult['DELIVERY_DOBROLAP'];

$isSplit = $storage->isSplit();

if (null !== $pickup) {
    /** @var Store $selectedShop */
    $selectedShop = $arResult['SELECTED_SHOP'];
    $stockResult = $pickup->getStockResult();

    if ($arResult['PARTIAL_PICKUP_AVAILABLE']) {
        $available = $arResult['PICKUP_STOCKS_AVAILABLE'];
    } else {
        $available = $stockResult->getAvailable();
    }

    // если нет доступных товаров, частичного получения не будет
    if ($available->isEmpty()) {
        $available = $stockResult->getDelayed();
        $availableQuantity = $available->getAmount();
        [$availableItems, $availableWeight] = $component->getOrderItemData($available);
        $availablePrice = $available->getPrice();
    } else {
        $availableQuantity = $available->getAmount();
        [$availableItems, $availableWeight] = $component->getOrderItemData($available);
        $availablePrice = $available->getPrice();

        $delayed = $stockResult->getDelayed();
        $delayedQuantity = $delayed->getAmount();
        [$delayedItems, $delayedWeight] = $component->getOrderItemData($delayed);
        $delayedPrice = $delayed->getPrice();

        if (!$delayed->isEmpty()) {
            $showDelayedItems = true;
        }
    }

    $pickupCanSplit = $arResult['SPLIT_PICKUP_AVAILABLE'];
    $pickupCanGetPartial = $arResult['PARTIAL_PICKUP_AVAILABLE'];

    $pickupIsSplit = $isSplit && $pickupCanSplit;
    $pickupIsPartial = $isSplit && $showDelayedItems;
}

$isPickup = $deliveryService->isPickup($selectedDelivery);
if ($isPickup && !($pickupIsPartial || $pickupIsSplit)) {
    $showPickupContainer = false;
}

$productsDeclension = new Declension('товар', 'товара', 'товаров');

if (null !== $delivery) {
    $deliveryResult = $delivery->getStockResult();
} elseif ($deliveryDobrolap) {
    $deliveryResult = $deliveryDobrolap->getStockResult();
} else {
    $deliveryResult = $pickup->getStockResult();
}
$deliveryOrderableResult = $deliveryResult->getOrderable();
$deliveryOrderableQuantity = $deliveryOrderableResult->getAmount();
[$deliveryOrderableItems, $deliveryOrderableWeight] = $component->getOrderItemData($deliveryOrderableResult);
$deliveryOrderablePrice = $deliveryOrderableResult->getPrice();

$deliveryIsSplit = $isSplit && !empty($arResult['SPLIT_RESULT']);
if (!empty($arResult['SPLIT_RESULT'])) {
    /** @var StockResultCollection $deliveryResult1 */
    $deliveryResult1 = $arResult['SPLIT_RESULT']['1']['DELIVERY']->getStockResult()->getOrderable();
    /** @var StockResultCollection $deliveryResult2 */
    $deliveryResult2 = $arResult['SPLIT_RESULT']['2']['DELIVERY']->getStockResult()->getOrderable();

    $deliveryResult1Quantity = $deliveryResult1->getAmount();
    [$deliveryResult1Items, $deliveryResult1Weight] = $component->getOrderItemData($deliveryResult1);
    $deliveryResult1Price = $deliveryResult1->getPrice();

    $deliveryResult2Quantity = $deliveryResult2->getAmount();
    [$deliveryResult2Items, $deliveryResult2Weight] = $component->getOrderItemData($deliveryResult2);
    $deliveryResult2Price = $deliveryResult2->getPrice();
}

$deliveryUnavailableResult = $deliveryResult->getUnavailable();
$deliveryUnavailableQuantity = $deliveryUnavailableResult->getAmount();
[$deliveryUnavailableItems, $deliveryUnavailableWeight] = $component->getOrderItemData($deliveryUnavailableResult);
$deliveryUnavailablePrice = $deliveryUnavailableResult->getPrice(false);
$deliveryIsSplit &= !($isPickup && !($pickupIsPartial || $pickupIsSplit));
?>
<?php /* отображается на 2 шаге, когда выбрана курьерская доставка */ ?>
<aside class="b-order__list js-list-orders-static <?= $deliveryUnavailableResult->isEmpty() ? '' : 'parts-type' ?>" <?= !$showPickupContainer ? '' : 'style="display:none"' ?>>
    <div class="one-delivery__block<?= $deliveryIsSplit ? '' : ' active activeBlock' ?>">
        <h4 class="b-title b-title--order-list js-popup-mobile-link js-full-list-title">
            <span class="js-mobile-title-order">Заказ: <span class="js-count-products"><?= $deliveryOrderableQuantity ?></span> <?= $productsDeclension->get($deliveryOrderableQuantity) ?>
            </span>
            (<?= WordHelper::showWeight($deliveryOrderableWeight, true) ?>) на
            сумму <?= CurrencyHelper::formatPrice(
                $deliveryOrderablePrice,
                false
            ) ?>
        </h4>
        <div class="b-order-list b-order-list--aside js-full-list js-popup-mobile">
            <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о
                заказе</a>
            <ul class="b-order-list__list js-order-list-block">
                <?php foreach ($deliveryOrderableItems as $item) { ?>
                    <li class="b-order-list__item b-order-list__item--aside js-full-list">
                        <div class="b-order-list__order-text b-order-list__order-text--aside js-full-list">
                            <div class="b-order-list__clipped-text">
                                <div class="b-order-list__text-backed">
                                    <?= $item['brand'] ?> <?= $item['name'] ?>
                                    <?php if ($item['quantity'] > 1) { ?>
                                        (<?= $item['quantity'] ?> шт)
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="b-order-list__order-value b-order-list__order-value--aside js-full-list">
                            <?= CurrencyHelper::formatPrice($item['price'], false) ?>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <h4 class="b-title b-title--order-list js-parts-list-title" data-count="<?= $deliveryUnavailableQuantity ?>"
            <?= $deliveryUnavailableResult->isEmpty() ? 'style="display:none"' : '' ?>>
            <span class="js-mobile-title-order">Останется в корзине: <span class="js-count-products"><?= $deliveryUnavailableQuantity ?></span></span>
            <?= $productsDeclension->get($deliveryUnavailableQuantity) ?>
            (<?= WordHelper::showWeight($deliveryUnavailableWeight, true) ?>) на
            сумму <?= CurrencyHelper::formatPrice(
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
                                    <?= $item['brand'] ?> <?= $item['name'] ?>
                                    <?php if ($item['quantity'] > 1) { ?>
                                        (<?= $item['quantity'] ?> шт)
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="b-order-list__order-value b-order-list__order-value--aside">
                            <?= CurrencyHelper::formatPrice($item['price'], false) ?>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <div class="b-order__link-wrapper js-back-to-basket"
            <?= $deliveryUnavailableResult->isEmpty() ? 'style="display:none"' : '' ?>>
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
    </div>
    <?php if (!empty($arResult['SPLIT_RESULT'])) { ?>
        <div class="two-deliveries__block<?= !$deliveryIsSplit ? '' : ' active activeBlock' ?>">
            <h4 class="b-title b-title--order-list js-popup-mobile-link js-full-list-title js-full-list-title--order-list js-popup-mobile-link">
                <span class="js-mobile-title-order">Заказ №1: <span class="js-count-products"><?= $deliveryResult1Quantity ?></span> <?= $productsDeclension->get($deliveryResult1Quantity) ?></span>
                (<?= WordHelper::showWeight($deliveryResult1Weight, true) ?>) на
                сумму <?= CurrencyHelper::formatPrice($deliveryResult1Price, false) ?>
            </h4>
            <div class="b-order-list b-order-list--aside js-full-list js-popup-mobile js-popup-mobile--aside js-full-list">
                <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о
                    заказе</a>
                <ul class="b-order-list__list js-order-list-block">
                    <?php foreach ($deliveryResult1Items as $item) { ?>
                        <li class="b-order-list__item b-order-list__item--aside js-full-list">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">
                                        <?= $item['brand'] ?> <?= $item['name'] ?>
                                        <?php if ($item['quantity'] > 1) { ?>
                                            (<?= $item['quantity'] ?> шт)
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">
                                <?= CurrencyHelper::formatPrice($item['price'], false) ?>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <h4 class="b-title b-title--order-list js-popup-mobile-link js-parts-list-title--order-list js-popup-mobile-link js-parts-list-title">
                <span class="js-mobile-title-order">Заказ №2: <span class="js-count-products"><?= $deliveryResult2Quantity ?></span> <?= $productsDeclension->get($deliveryResult2Quantity) ?></span>
                (<?= WordHelper::showWeight($deliveryResult2Weight, true) ?>) на
                сумму <?= CurrencyHelper::formatPrice($deliveryResult2Price, false) ?>
            </h4>
            <div class="b-order-list b-order-list--aside js-popup-mobile js-popup-mobile--aside js-parts-list">
                <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о
                    заказе</a>
                <ul class="b-order-list__list js-order-list-block">
                    <?php foreach ($deliveryResult2Items as $item) { ?>
                        <li class="b-order-list__item b-order-list__item--aside js-full-list">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">
                                        <?= $item['brand'] ?> <?= $item['name'] ?>
                                        <?php if ($item['quantity'] > 1) { ?>
                                            (<?= $item['quantity'] ?> шт)
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">
                                <?= CurrencyHelper::formatPrice($item['price'], false) ?>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <h4 class="b-title b-title--order-list js-incart-title"
                <?= $deliveryUnavailableResult->isEmpty() ? 'style="display:none"' : '' ?>>
                <span class="js-mobile-title-order">Останется в корзине: <span class="js-count-products"><?= $deliveryUnavailableQuantity ?></span></span>
                <?= $productsDeclension->get($deliveryUnavailableQuantity) ?>
                (<?= WordHelper::showWeight($deliveryUnavailableWeight, true) ?>) на
                сумму <?= CurrencyHelper::formatPrice(
                    $deliveryUnavailablePrice,
                    false
                ) ?>
            </h4>
            <div class="b-order-list b-order-list--aside js-popup-mobile js-incart-block"
                <?= $deliveryUnavailableResult->isEmpty() ? 'style="display:none"' : '' ?>>
                <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о
                    заказе</a>
                <ul class="b-order-list__list js-order-list-block">
                    <?php foreach ($deliveryUnavailableItems as $item) { ?>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">
                                        <?= $item['brand'] ?> <?= $item['name'] ?>
                                        <?php if ($item['quantity'] > 1) { ?>
                                            (<?= $item['quantity'] ?> шт)
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">
                                <?= CurrencyHelper::formatPrice($item['price'], false) ?>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="b-order__link-wrapper js-back-to-basket"
                <?= $deliveryUnavailableResult->isEmpty() ? 'style="display:none"' : '' ?>>
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
        </div>
    <?php } ?>
    <div class="mobile-delivery__block"></div>
</aside>
<?php /* отображается на 2 шаге, когда выбран самовывоз */ ?>
<aside class="b-order__list js-list-orders-cont" <?= $showPickupContainer ? '' : 'style="display:none"' ?>>
    <h4 class="b-title b-title--order-list js-popup-mobile-link js-full-list-title">
        <?= $pickupCanSplit ? 'Заказ №1' : 'Заказ' ?>
        : <?= $availableQuantity ?> <?= $productsDeclension->get($availableQuantity) ?>
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
                                <?= $item['brand'] ?> <?= $item['name'] ?>
                                <?php if ($item['quantity'] > 1) { ?>
                                    (<?= $item['quantity'] ?> шт)
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value">
                        <?= CurrencyHelper::formatPrice($item['price'], false) ?>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
    <h4 class="b-title b-title--order-list js-parts-list-title"
        <?= !($pickupCanSplit || $pickupCanGetPartial) ? 'style="display:none"' : '' ?>>
        <span class="js-mobile-title-order"><?= $pickupCanSplit ? 'Заказ №2' : 'Останется в корзине' ?>
            : <span class="js-count-products"><?= $delayedQuantity ?></span></span>
        <?= $productsDeclension->get($delayedQuantity) ?> (<?= WordHelper::showWeight($delayedWeight, true) ?>) на
        сумму <?= CurrencyHelper::formatPrice(
            $delayedPrice,
            false
        ) ?>
    </h4>
    <div class="b-order-list b-order-list--aside js-popup-mobile js-parts-list"
        <?= !($pickupCanSplit || $pickupCanGetPartial) ? 'style="display:none"' : '' ?>>
        <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о
            заказе</a>
        <ul class="b-order-list__list js-order-list-block">
            <?php foreach ($delayedItems as $item) { ?>
                <li class="b-order-list__item b-order-list__item--aside">
                    <div class="b-order-list__order-text b-order-list__order-text--aside">
                        <div class="b-order-list__clipped-text">
                            <div class="b-order-list__text-backed">
                                <?= $item['brand'] ?> <?= $item['name'] ?>
                                <?php if ($item['quantity'] > 1) { ?>
                                    (<?= $item['quantity'] ?> шт)
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value b-order-list__order-value--aside">
                        <?= CurrencyHelper::formatPrice($item['price'], false) ?>
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
