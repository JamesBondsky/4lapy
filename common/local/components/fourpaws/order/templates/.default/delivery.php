<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Sale\Basket;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\StoreBundle\Entity\Store;

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 */

/** @var CalculationResultInterface $delivery */
$delivery = $arResult['DELIVERY'];
/** @var CalculationResultInterface $pickup */
$pickup = $arResult['PICKUP'];
/** @var CalculationResultInterface $selectedDelivery */
$selectedDelivery = $arResult['SELECTED_DELIVERY'];
$selectedDeliveryId = $arResult['SELECTED_DELIVERY_ID'];

/** @noinspection PhpUnhandledExceptionInspection */
/** @var DeliveryService $deliveryService */
$deliveryService = Application::getInstance()->getContainer()->get('delivery.service');

/** @var Store $selectedShop */
$selectedShop = $arResult['SELECTED_SHOP'];

/** @var Basket $basket */
$basket = $arResult['BASKET'];

/** @var OrderStorage $storage */
$storage = $arResult['STORAGE'];

$selectedShopCode = '';
$isPickup = false;
if ($pickup && $selectedDelivery->getDeliveryCode() === $pickup->getDeliveryCode()) {
    $selectedShopCode = $arResult['SELECTED_SHOP']->getXmlId();
    $isPickup = true;
}

?>
<div class="b-container">
    <h1 class="b-title b-title--h1 b-title--order">
        <?php $APPLICATION->ShowTitle() ?>
    </h1>
    <div class="b-order js-order-whole-block">
        <div class="b-tab-list">
            <ul class="b-tab-list__list js-scroll-order">
                <li class="b-tab-list__item completed">
                    <a class="b-tab-list__link"
                       href="<?= $arResult['URL']['AUTH'] ?>"
                       title="">
                        <span class="b-tab-list__step">Шаг </span>1. Контактные данные
                    </a>
                </li>
                <li class="b-tab-list__item active js-active-order-step">
                    <span class="b-tab-list__step">Шаг </span>2. Выбор доставки
                </li>
                <li class="b-tab-list__item">
                    <span class="b-tab-list__step">Шаг </span>3. Выбор оплаты
                </li>
                <li class="b-tab-list__item">
                    Завершение
                </li>
            </ul>
        </div>
        <div class="b-order__block b-order__block--step-two">
            <div class="b-order__content js-order-content-block">
                <article class="b-order-contacts">
                    <header class="b-order-contacts__header">
                        <h2 class="b-title b-title--order-tab">Удобный для вас способ получения в</h2>
                        <a class="b-link b-link--select b-link--order-step js-open-popup"
                           href="javascript:void(0);"
                           title="<?= $arResult['SELECTED_CITY']['NAME'] ?>"
                           data-popup-id="pick-city">
                            <?= $arResult['SELECTED_CITY']['TYPE'] === LocationService::TYPE_CITY ? 'г. ' : '' ?><?= $arResult['SELECTED_CITY']['NAME'] ?>
                        </a>
                    </header>
                    <form class="b-order-contacts__form b-order-contacts__form--choose-delivery js-form-validation"
                          data-url="<?= $arResult['URL']['DELIVERY_VALIDATION'] ?>"
                          method="post"
                          id="order-step">
                        <input type="hidden" name="shopId" class="js-no-valid"
                               value="<?= /** @noinspection PhpUnhandledExceptionInspection */
                               $pickup ? $pickup->getSelectedStore()->getXmlId() : '' ?>">
                        <input type="hidden" name="delyveryType"
                               value="<?= $storage->isSplit() ? 'twoDeliveries' : 'oneDelivery' ?>" class="js-no-valid">
                        <div class="b-choice-recovery b-choice-recovery--order-step">
                            <?php if ($delivery) {
                                ?>
                                <input class="b-choice-recovery__input js-recovery-telephone js-delivery"
                                       id="order-delivery-address"
                                       type="radio"
                                       name="deliveryId"
                                    <?= $deliveryService->isDelivery($selectedDelivery) ? 'checked="checked"' : '' ?>
                                       value="<?= $delivery->getDeliveryId() ?>"
                                       data-delivery="<?= $delivery->getPrice() ?>"
                                       data-full="<?= $basket->getPrice() ?>"
                                       data-check="js-list-orders-static"/>
                                <label class="b-choice-recovery__label b-choice-recovery__label--left b-choice-recovery__label--order-step"
                                       for="order-delivery-address">
                                    <span class="b-choice-recovery__main-text">
                                        <span class="b-choice-recovery__main-text">
                                            <span class="b-choice-recovery__first">Доставка</span>
                                            <span class="b-choice-recovery__second">курьером</span>
                                        </span>
                                    </span>
                                    <span class="b-choice-recovery__addition-text">
                                        <?= /** @noinspection PhpUnhandledExceptionInspection */
                                        DeliveryTimeHelper::showTime($delivery) ?>,
                                        <?= mb_strtolower(CurrencyHelper::formatPrice($delivery->getPrice(), true)) ?>
                                    </span>
                                    <span class="b-choice-recovery__addition-text b-choice-recovery__addition-text--mobile">
                                        <?= /** @noinspection PhpUnhandledExceptionInspection */
                                        DeliveryTimeHelper::showTime($delivery, ['SHORT' => true]) ?>,
                                        <?= CurrencyHelper::formatPrice($delivery->getPrice(), false) ?>
                                </label>
                                <?php
                            } ?>
                            <?php if ($pickup) {
                                ?>
                                <?php
                                $available = $pickup->getStockResult()->getAvailable();
                                if (!$available->isEmpty() && $storage->isPartialGet()) {
                                    $price = $available->getPrice();
                                } else {
                                    $price = $pickup->getStockResult()->getPrice();
                                }
                                ?>
                                <input class="b-choice-recovery__input js-recovery-email js-myself-shop js-delivery"
                                       id="order-delivery-pick-up"
                                       type="radio"
                                       name="deliveryId"
                                    <?= $deliveryService->isPickup($selectedDelivery) ? 'checked="checked"' : '' ?>
                                       value="<?= $pickup->getDeliveryId() ?>"
                                       data-delivery="<?= $pickup->getPrice() ?>"
                                       data-full="<?= $price ?>"
                                       data-check="js-list-orders-cont"/>
                                <label class="b-choice-recovery__label b-choice-recovery__label--right b-choice-recovery__label--order-step js-open-popup"
                                       for="order-delivery-pick-up"
                                       data-popup-id="popup-order-stores">
                                    <span class="b-choice-recovery__main-text">Самовывоз</span>
                                    <span class="b-choice-recovery__addition-text js-my-pickup js-pickup-tab">
                                        <?= /** @noinspection PhpUnhandledExceptionInspection */
                                        DeliveryTimeHelper::showTime(
                                            $pickup,
                                            [
                                                'SHOW_TIME' => !$deliveryService->isDpdPickup(
                                                    $pickup
                                                ),
                                            ]
                                        ) ?>, <?= mb_strtolower(CurrencyHelper::formatPrice($pickup->getPrice(),
                                            true)) ?>
                                    </span>
                                    <span class="b-choice-recovery__addition-text b-choice-recovery__addition-text--mobile js-my-pickup js-pickup-tab">
                                        <?= /** @noinspection PhpUnhandledExceptionInspection */
                                        DeliveryTimeHelper::showTime(
                                            $pickup,
                                            [
                                                'SHORT' => true,
                                                'SHOW_TIME' => !$deliveryService->isDpdPickup(
                                                    $pickup
                                                ),
                                            ]
                                        ) ?>, <?= CurrencyHelper::formatPrice($pickup->getPrice(), false) ?>
                                    </span>
                                </label>
                                <?php
                            } ?>
                        </div>
                        <ul class="b-radio-tab js-myself-shop">
                            <?php if ($delivery) {
                                ?>
                                <li class="b-radio-tab__tab js-telephone-recovery"
                                    <?= $selectedDeliveryId !== $delivery->getDeliveryId() ? 'style="display:none"' : '' ?>>
                                    <?php include 'include/delivery.php' ?>
                                </li>
                                <?php
                            } ?>
                            <?php if ($pickup) {
                                ?>
                                <li class="b-radio-tab__tab js-email-recovery"
                                    <?= $selectedDeliveryId !== $pickup->getDeliveryId() ? 'style="display:none"' : '' ?>>
                                    <?php include 'include/pickup.php' ?>
                                </li>
                                <?php
                            } ?>
                        </ul>
                    </form>
                </article>
            </div>
            <?php include 'include/basket.php' ?>
        </div>
        <?php
        $basketPrice = $basket->getPrice();
        if ($isPickup) {
            $stockResultByShop = $selectedDelivery->getStockResult();
            if ($storage->isPartialGet()) {
                $basketPrice = $stockResultByShop->getAvailable()->getPrice();
            } else {
                $basketPrice = $stockResultByShop->getPrice();
            }
        }
        ?>
        <div class="b-order-list b-order-list--cost b-order-list--order-step-two js-order-next">
            <ul class="b-order-list__list b-order-list__list--cost">
                <li class="b-order-list__item b-order-list__item--cost b-order-list__item--order-step-two">
                    <div class="b-order-list__order-text b-order-list__order-text--order-step-two">
                        <div class="b-order-list__clipped-text">
                            <div class="b-order-list__text-backed">
                                Товары с учетом всех скидок
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value b-order-list__order-value--order-step-two js-price-full">
                        <?= CurrencyHelper::formatPrice($basketPrice, false) ?>
                    </div>
                </li>
                <li class="b-order-list__item b-order-list__item--cost b-order-list__item--order-step-two">
                    <div class="b-order-list__order-text b-order-list__order-text--order-step-two">
                        <div class="b-order-list__clipped-text">
                            <div class="b-order-list__text-backed">
                                Доставка
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value b-order-list__order-value--order-step-two js-price-deliv">
                        <?= CurrencyHelper::formatPrice($selectedDelivery->getPrice(), false) ?>
                    </div>
                </li>
                <li class="b-order-list__item b-order-list__item--cost b-order-list__item--order-step-two">
                    <div class="b-order-list__order-text b-order-list__order-text--order-step-two">
                        <div class="b-order-list__clipped-text">
                            <div class="b-order-list__text-backed">
                                Итого к оплате
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value b-order-list__order-value--order-step-two js-price-total">
                        <?= CurrencyHelper::formatPrice($basketPrice + $selectedDelivery->getPrice(), false) ?>
                    </div>
                </li>
            </ul>
        </div>
        <button class="b-button b-button--social b-button--next b-button--fixed-bottom js-order-next js-valid-out-sub">
            Далее
        </button>
    </div>
</div>
