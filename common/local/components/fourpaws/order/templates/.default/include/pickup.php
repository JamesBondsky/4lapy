<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResult;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\StoreBundle\Entity\Store;

/**
 * @var array $arResult
 * @var array $arParams
 * @var CalculationResultInterface $pickup
 * @var
 */

/** @var DeliveryService $deliveryService */
$deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
/** @var OrderStorage $storage */
$storage = $arResult['STORAGE'];

/** @var Store $selectedShop */
$selectedShop = $arResult['SELECTED_SHOP'];

/** @var StockResultCollection $available */
$available = $arResult['PICKUP_STOCKS_AVAILABLE'];
/** @var StockResultCollection $delayed */
$delayed = $arResult['PICKUP_STOCKS_DELAYED'];

$metro = $arResult['METRO'][$selectedShop->getMetro()];

$canGetPartial = $arResult['PARTIAL_PICKUP_AVAILABLE'];
$canSplit = $arResult['SPLIT_PICKUP_AVAILABLE'];
$partialGet = $storage->isSplit() && ($canGetPartial || $canSplit);
$partialPickup = $arResult['PARTIAL_PICKUP'] ?? $pickup;
$metro = $arResult['METRO'][$selectedShop->getMetro()];
?>

<li class="b-radio-tab__tab js-email-recovery">
    <div class="b-input-line b-input-line--address b-input-line--myself">
        <div class="b-input-line__label-wrapper">
            <span class="b-input-line__label">Адрес доставки</span>
        </div>
        <ul class="b-delivery-list">
            <li class="b-delivery-list__item b-delivery-list__item--myself">
                <span class="b-delivery-list__link b-delivery-list__link--myself">
                    <?php if ($metro) { ?>
                        <span class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--<?= $metro['BRANCH']['UF_COLOR'] ?>"></span>
                        <?= 'м. ' . $metro['UF_NAME'] . ', ' . $selectedShop->getAddress() ?>
                    <?php } else { ?>
                        <?= $selectedShop->getAddress() ?>
                    <?php } ?>
                </span>
            </li>
        </ul>
    </div>
    <div class="b-input-line b-input-line--myself">
        <div class="b-input-line__label-wrapper">
            <span class="b-input-line__label">Время работы</span>
        </div>
        <div class="b-input-line__text-line b-input-line__text-line--myself">
            <?= $selectedShop->getScheduleString() ?>
        </div>
    </div>
    <div class="b-input-line b-input-line--myself">
        <div class="b-input-line__label-wrapper">
            <span class="b-input-line__label">Оплата</span>
        </div>
        <div class="b-input-line__text-line">
            <?php foreach ($arResult['PICKUP_AVAILABLE_PAYMENTS'] as $payment) {
                $icon = $payment['CODE'] === OrderService::PAYMENT_CASH ? 'icon-cash' : 'icon-bank-card'
                ?>
                <span class="b-input-line__pay-type">
                    <span class="b-icon b-icon--icon-cash">
                        <?= new SvgDecorator($icon, 16, 12) ?>
                    </span>
                    <?= $payment['NAME'] ?>
                </span>
            <?php } ?>
        </div>
    </div>
    <div class="b-input-line b-input-line--partially">
        <div class="b-input-line__label-wrapper b-input-line__label-wrapper--order-full">
            <span class="b-input-line__label js-parts-info">
                <?php if ($canGetPartial || $canSplit) { ?>
                    Заказ в наличии частично
                <?php } elseif ($available->isEmpty()) { ?>
                    Требуется ждать поставки со склада
                <?php } else { ?>
                    Заказ доступен в полном составе
                <?php } ?>
            </span>
        </div>
        <div class="b-radio b-radio--tablet-big" <?= ($canGetPartial || $canSplit) ? '' : 'style="display:none"' ?>>
            <input class="b-radio__input ok"
                   type="radio"
                   name="order-pick-time"
                   id="order-pick-time-now"
                <?= $partialGet ? 'checked="checked"' : '' ?>
                   value="1"
                   data-radio="5">
            <label class="b-radio__label b-radio__label--tablet-big" for="order-pick-time-now">
            </label>
            <div class="b-order-list b-order-list--myself js-parts-price js-price-block">
                <ul class="b-order-list__list">
                    <li class="b-order-list__item b-order-list__item--myself js-parts-price js-price-block">
                        <div class="b-order-list__order-text b-order-list__order-text--myself js-parts-price js-price-block">
                            <div class="b-order-list__clipped-text">
                                <div class="b-order-list__text-backed js-my-pickup js-pickup-time">
                                    Забрать <?= DeliveryTimeHelper::showTime($partialPickup,
                                        ['SHOW_TIME' => $pickup instanceof PickupResult]) ?></div>
                            </div>
                        </div>
                        <div class="b-order-list__order-value b-order-list__order-value--myself js-parts-price js-price-block">
                            <?= CurrencyHelper::formatPrice($canGetPartial ? $available->getPrice() : $pickup->getStockResult()->getPrice()) ?>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="b-radio__addition-text js-excluded-parts" <?= $delayed->isEmpty() ? 'style="display:none"' : '' ?>>
                <p>За исключением:</p>
                <ol class="js-delay-items">
                    <?php /** @var StockResult $item */ ?>
                    <?php foreach ($delayed as $item) { ?>
                        <li>
                            <?= $item->getOffer()->getName() ?> <?= ($item->getAmount() > 1) ? ('(' . $item->getAmount() . ' шт)') : '' ?>
                        </li>
                    <?php } ?>
                </ol>
            </div>
        </div>
        <div class="b-radio b-radio--tablet-big">
            <input class="b-radio__input ok"
                   type="radio"
                   name="order-pick-time"
                   id="order-pick-time-then"
                <?= !$partialGet ? 'checked="checked"' : '' ?>
                   value="0"
                   data-radio="6">
            <label class="b-radio__label b-radio__label--tablet-big" for="order-pick-time-then">
            </label>
            <div class="b-order-list b-order-list--myself js-full-price js-price-block">
                <ul class="b-order-list__list">
                    <li class="b-order-list__item b-order-list__item--myself js-full-price js-price-block">
                        <div class="b-order-list__order-text b-order-list__order-text--myself js-full-price js-price-block">
                            <div class="b-order-list__clipped-text">
                                <div class="b-order-list__text-backed">Забрать полный заказ
                                </div>
                            </div>
                        </div>
                        <div class="b-order-list__order-value b-order-list__order-value--myself js-full-price js-price-block">
                            <?= CurrencyHelper::formatPrice($pickup->getStockResult()->getPrice()) ?>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="b-radio__addition-text">
                <p class="js-pickup_full js-pickup-time">
                    <?= DeliveryTimeHelper::showTime($pickup) ?>
                </p>
            </div>
        </div>
    </div>
    <a class="b-link b-link--another-point js-open-popup"
       href="javascript:void(0);"
       data-popup-id="popup-order-stores"
       title="">
        Выбрать другой пункт самовывоза
    </a>
</li>
