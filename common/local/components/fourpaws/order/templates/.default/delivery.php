<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Sale\Basket;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\StoreBundle\Entity\Store;

/**
 * @bxnolanginspection
 *
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var FourPawsOrderComponent $component
 */

/** @var CalculationResultInterface $delivery */
$delivery = $arResult['DELIVERY'];
/** @var CalculationResultInterface $deliveryDostavista */
$deliveryDostavista = $arResult['DELIVERY_DOSTAVISTA'];
/** @var CalculationResultInterface $deliveryDobrolap */
$deliveryDobrolap = $arResult['DELIVERY_DOBROLAP'];
/** @var PickupResultInterface $pickup */
$pickup = $arResult['PICKUP'];
/** @var CalculationResultInterface $selectedDelivery */
$selectedDelivery = $arResult['SELECTED_DELIVERY'];

$deliveryService = $component->getDeliveryService();

/** @var Store $selectedShop */
$selectedShop = $arResult['SELECTED_SHOP'];

/** @var Basket $basket */
$basket = $arResult['BASKET'];

/** @var OrderStorage $storage */
$storage = $arResult['STORAGE'];

$subscribeIntervals = $component->getOrderSubscribeService()->getFrequencies();

$daysOfWeek = ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'];

$selectedShopCode = '';
$isPickup = false;
if ($pickup && $selectedDelivery->getDeliveryCode() === $pickup->getDeliveryCode()) {
    $selectedShopCode = $arResult['SELECTED_SHOP']->getXmlId();
    $isPickup = true;
}

if ($arResult['ECOMMERCE_VIEW_SCRIPT']) {
    echo $arResult['ECOMMERCE_VIEW_SCRIPT'];
} ?>
<div class="b-container">
    <h1 class="b-title b-title--h1 b-title--order">
        <?php $APPLICATION->ShowTitle() ?>
    </h1>
    <div class="b-order js-order-whole-block js-order-step2">
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
                        <a class="b-link b-link--select b-link--order-step js-open-popup" href="javascript:void(0);" title="<?= $arResult['SELECTED_CITY']['NAME'] ?>" data-popup-id="pick-city">
                            <?= $arResult['SELECTED_CITY']['TYPE'] === LocationService::TYPE_CITY ? 'г. ' : '' ?><?= $arResult['SELECTED_CITY']['NAME'] ?>
                        </a>
                    </header>
                    <form class="b-order-contacts__form b-order-contacts__form--choose-delivery js-form-validation" method="post" id="order-step" data-url="<?= $arResult['URL']['DELIVERY_VALIDATION'] ?>"
                        <?= ($storage->isSubscribe()) ? 'data-form-step2-subscribe="true"' : '' ?>>
                        <input type="hidden" name="shopId" class="js-no-valid"
                               value="<?= /** @noinspection PhpUnhandledExceptionInspection */
                               $pickup ? $pickup->getSelectedShop()->getXmlId() : '' ?>">
                        <input type="hidden" name="delyveryType" class="js-no-valid"
                               value="<?= (!empty($arResult['SPLIT_RESULT']) && $storage->isSplit()) ? 'twoDeliveries' : 'oneDelivery' ?>">
                        <input type="hidden" name="deliveryTypeId"
                               value="<?php
                               if ($selectedDelivery) {
                                   echo $selectedDelivery->getDeliveryId();
                               } else if ($delivery) {
                                   echo $delivery->getDeliveryId();
                               } else if ($pickup) {
                                   echo $pickup->getDeliveryId();
                               } else if ($deliveryDostavista) {
                                   echo $deliveryDostavista->getDeliveryId();
                               } else if ($deliveryDobrolap) {
                                   echo $deliveryDobrolap->getDeliveryId();
                               } ?>"
                               class="js-no-valid">
                        <input type="hidden" name="deliveryCoords" value="">

                        <div class="b-choice-recovery b-choice-recovery--order-step">
                            <?php if ($delivery) {
                                $selectedDel = ($selectedDelivery->getDeliveryCode() === DeliveryService::DELIVERY_DOSTAVISTA_CODE || $selectedDelivery->getDeliveryCode() === DeliveryService::INNER_DELIVERY_CODE) ? $delivery : $selectedDelivery; ?>
                                <input <?= $deliveryService->isDelivery($selectedDel) ? 'checked="checked"' : '' ?>
                                        class="b-choice-recovery__input js-recovery-telephone js-delivery"
                                        data-set-delivery-type="<?= $delivery->getDeliveryId() ?>"
                                        data-is-dostavista="0"
                                        id="order-delivery-address"
                                        type="radio"
                                        name="deliveryId"
                                        data-text="Доставка курьером"
                                        value="<?= $delivery->getDeliveryId() ?>"
                                        data-delivery="<?= $delivery->getPrice() ?>"
                                        data-full="<?= $delivery->getStockResult()->getOrderable()->getPrice() ?>"
                                        data-check="js-list-orders-static"/>
                                <label class="b-choice-recovery__label b-choice-recovery__label--left b-choice-recovery__label--order-step" for="order-delivery-address">
                                    <span class="b-choice-recovery__main-text">
                                        <span class="b-choice-recovery__main-text">
                                            <span class="b-choice-recovery__first">Доставка</span>
                                            <span class="b-choice-recovery__second">курьером</span>
                                        </span>
                                    </span>
                                    <span class="b-choice-recovery__addition-text js-cur-pickup">
                                        <?= /** @noinspection PhpUnhandledExceptionInspection */
                                        DeliveryTimeHelper::showTime($delivery) ?>, <span class="js-delivery--price"><?= $delivery->getPrice() ?></span>₽
                                    </span>
                                    <span class="b-choice-recovery__addition-text b-choice-recovery__addition-text--mobile js-cur-pickup-mobile">
                                        <?= /** @noinspection PhpUnhandledExceptionInspection */
                                        DeliveryTimeHelper::showTime($delivery, ['SHORT' => true]) ?>, <span class="js-delivery--price"><?= $delivery->getPrice() ?></span>₽
                                    </span>
                                </label>
                            <?php }

                            if ($pickup) {
                                $available = $arResult['PICKUP_STOCKS_AVAILABLE'];
                                if ($arResult['PARTIAL_PICKUP_AVAILABLE'] && $storage->isSplit()) {
                                    $price = $available->getPrice();
                                } else {
                                    $price = $pickup->getStockResult()->getPrice();
                                } ?>
                                <input <?= $deliveryService->isPickup($selectedDelivery) ? 'checked="checked"' : '' ?>
                                        class="b-choice-recovery__input js-recovery-email js-myself-shop js-delivery"
                                        data-set-delivery-type="<?= $pickup->getDeliveryId() ?>"
                                        data-is-dostavista="0"
                                        id="order-delivery-pick-up"
                                        type="radio"
                                        name="deliveryId"
                                        data-text="Самовывоз"
                                        value="<?= $pickup->getDeliveryId() ?>"
                                        data-delivery="<?= $pickup->getPrice() ?>"
                                        data-full="<?= $price ?>"
                                        data-check="js-list-orders-cont"/>
                                <label class="b-choice-recovery__label b-choice-recovery__label--right b-choice-recovery__label--order-step js-open-popup" for="order-delivery-pick-up" data-popup-id="popup-order-stores">
                                    <span class="b-choice-recovery__main-text">Самовывоз</span>
                                    <span class="b-choice-recovery__addition-text js-my-pickup js-pickup-tab">
                                        <?= /** @noinspection PhpUnhandledExceptionInspection */
                                        DeliveryTimeHelper::showTime($pickup, ['SHOW_TIME' => !$deliveryService->isDpdPickup($pickup)]) ?>, <?= mb_strtolower(CurrencyHelper::formatPrice($pickup->getPrice(), true)) ?>
                                    </span>
                                    <span class="b-choice-recovery__addition-text b-choice-recovery__addition-text--mobile js-my-pickup js-pickup-tab">
                                        <?= /** @noinspection PhpUnhandledExceptionInspection */
                                        DeliveryTimeHelper::showTime($pickup, ['SHORT' => true, 'SHOW_TIME' => !$deliveryService->isDpdPickup($pickup)]) ?>, <?= CurrencyHelper::formatPrice($pickup->getPrice(), false) ?>
                                    </span>
                                </label>
                            <?php } ?>

                            <?php if (!$delivery && $deliveryDostavista) { ?>
                                <input <?= $deliveryService->isDostavistaDelivery($selectedDelivery) ? 'checked="checked"' : '' ?>
                                        class="b-choice-recovery__input js-recovery-dostavista js-delivery"
                                        data-set-delivery-type="<?= $deliveryDostavista->getDeliveryId() ?>"
                                        data-is-dostavista="1"
                                        id="order-delivery-address"
                                        type="radio"
                                        name="deliveryId"
                                        data-text="Экспресс доставка"
                                        value="<?= $deliveryDostavista->getDeliveryId() ?>"
                                        data-delivery="<?= $deliveryDostavista->getPrice() ?>"
                                        data-full="<?= $deliveryDostavista->getStockResult()->getOrderable()->getPrice() ?>"
                                        data-check="js-list-orders-static"/>
                                <label class="b-choice-recovery__label b-choice-recovery__label--left b-choice-recovery__label--order-step" for="order-delivery-address">
                                    <span class="b-choice-recovery__main-text">
                                        <span class="b-choice-recovery__main-text">
                                            Экспресс доставка
                                        </span>
                                    </span>
                                    <span class="b-choice-recovery__addition-text js-cur-pickup">
                                        В&nbsp;течение <?= round($deliveryDostavista->getPeriodTo() / 60) ?>&nbsp;часов, <?= $deliveryDostavista->getPrice() ?>&nbsp;₽
                                    </span>
                                    <span class="b-choice-recovery__addition-text b-choice-recovery__addition-text--mobile js-cur-pickup-mobile">
                                        В&nbsp;течение <?= round($deliveryDostavista->getPeriodTo() / 60) ?>&nbsp;часов, <?= $deliveryDostavista->getPrice() ?>&nbsp;₽
                                    </span>
                                </label>
                            <?php } ?>

                            <?php if ($deliveryDobrolap) { ?>
                                <div class="b-choice-recovery__tooltip" data-b-choice-recovery-tooltip>
                                    <input <?= $deliveryService->isDobrolapDelivery($selectedDelivery) ? 'checked="checked"' : '' ?>
                                            class="b-choice-recovery__input js-recovery-dobrolap js-myself-shop js-delivery"
                                            data-set-delivery-type="<?= $deliveryDobrolap->getDeliveryId() ?>"
                                            data-is-dostavista="0"
                                            id="order-delivery-dobrolap"
                                            type="radio"
                                            name="deliveryId"
                                            data-text="Самовывоз"
                                            value="<?= $deliveryDobrolap->getDeliveryId() ?>"
                                            data-delivery="<?= $deliveryDobrolap->getPrice() ?>"
                                            data-full="<?= $deliveryDobrolap->getStockResult()->getOrderable()->getPrice() ?>"
                                            data-check="js-list-orders-cont"/>
                                    <label class="b-choice-recovery__label b-choice-recovery__label--right b-choice-recovery__label--order-step b-choice-recovery__label--with-icon b-choice-recovery__label--mt js-open-popup" for="order-delivery-dobrolap" data-popup-id="popup-order-shelters">
                                        <img src="/static/build/images/content/dobrolap/dobrolap-logo.png" alt="" srcset="/static/build/images/content/dobrolap/dobrolap-logo@2x.png 2x, /static/build/images/content/dobrolap/dobrolap-logo@3x.png 3x" class="b-choice-recovery__label-icon"/>
                                        <div>
                                            <span class="b-choice-recovery__main-text">Доставка в приют</span>
                                            <span class="b-choice-recovery__addition-text">бесплатно</span>
                                            <span class="b-choice-recovery__addition-text b-choice-recovery__addition-text--mobile">бесплатно</span>
                                        </div>
                                    </label>
                                    <button type="button" class="b-choice-recovery__tooltip-trigger" data-b-choice-recovery-tooltip="trigger">
                                        Информация
                                    </button>

                                    <div class="b-choice-recovery__tooltip-content" data-b-choice-recovery-tooltip="content">
                                        Ваш заказ будет доставлен в&nbsp;выбранный Вами приют для&nbsp;бездомных животных.
                                        После оплаты заказа вы получите сюрприз и&nbsp;памятный магнит.
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <ul class="b-radio-tab js-myself-shop">
                            <?php if ($delivery) {
                                $isHidden = $selectedDelivery->getDeliveryId() !== $delivery->getDeliveryId();
                                ?>
                                <li class="b-radio-tab__tab js-telephone-recovery" <?= $isHidden ? 'style="display:none"' : '' ?>>
                                    <?php include 'include/delivery.php' ?>
                                </li>
                            <?php } ?>
                            <?php if ($pickup) {
                                $isHidden = $selectedDelivery->getDeliveryId() !== $pickup->getDeliveryId();
                                ?>
                                <li class="b-radio-tab__tab js-email-recovery" <?= $isHidden ? 'style="display:none"' : '' ?>>
                                    <?php include 'include/pickup.php' ?>
                                </li>
                            <?php } ?>
                            <?php if (!$delivery && $deliveryDostavista) {
                                $isHidden = $selectedDelivery->getDeliveryId() !== $deliveryDostavista->getDeliveryId();
                                ?>
                                <li class="b-radio-tab__tab js-dostavista-recovery" <?= $isHidden ? 'style="display:none"' : '' ?>>
                                    <?php include 'include/dostavista.php' ?>
                                </li>
                            <?php } ?>
                            <?php if ($deliveryDobrolap) {
                                $isHidden = $selectedDelivery->getDeliveryId() !== $deliveryDobrolap->getDeliveryId();
                                ?>
                                <li class="b-radio-tab__tab js-dobrolap-recovery" <?= $isHidden ? 'style="display:none"' : '' ?>>
                                    <?php include 'include/dobrolap.php' ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </form>
                </article>
            </div>
            <?php include 'include/basket.php' ?>
        </div>

        <?php
        if ($isPickup) {
            $available = $arResult['PICKUP_STOCKS_AVAILABLE'];
            if ($arResult['PARTIAL_PICKUP_AVAILABLE'] && $storage->isSplit()) {
                $basketPrice = $available->getPrice();
            } else {
                $basketPrice = $pickup->getStockResult()->getPrice();
            }
        } elseif ($delivery) {
            $basketPrice = $delivery->getStockResult()->getOrderable()->getPrice();
        } elseif ($deliveryDobrolap) {
            $basketPrice = $deliveryDobrolap->getStockResult()->getOrderable()->getPrice();
        } ?>

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
        <button class="b-button b-button--social <?= ($storage->isSubscribe()) ? 'b-button--next-subscribe-delivery' : 'b-button--next' ?> b-button--fixed-bottom js-order-next js-valid-out-sub">
            Далее
        </button>
    </div>
</div>
<?php
$currentShopInfo = $pickup ? $component->getShopListService()->toArray(
    $component->getShopListService()->getOneShopInfo($pickup->getSelectedShop()->getXmlId(), $storage, $pickup)
) : [];
?>
<script>
    window.fullBasket = <?= CUtil::PhpToJSObject(array_values($component->getBasketItemData($basket))) ?>;
    window.currentShop = <?= CUtil::PhpToJSObject($currentShopInfo) ?>;
</script>
