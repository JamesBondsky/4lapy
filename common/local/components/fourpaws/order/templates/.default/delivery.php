<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\Location\LocationService;
use FourPaws\Helpers\CurrencyHelper;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Main\Grid\Declension;

/**
 * @var array $arParams
 * @var array $arResult
 */

function showDeliveryTime(CalculationResult $calculationResult, $short = false)
{
    $result = '';
    switch ($calculationResult->getPeriodType()) {
        case CalculationResult::PERIOD_TYPE_DAY:
            $date = new DateTime();
            $date->modify('+' . ($calculationResult->getPeriodFrom()) . ' days');
            if ($short) {
                $result = FormatDate('l, d F', $date->getTimestamp());
            } else {
                $result = FormatDate('l, d F', $date->getTimestamp());
            }

            break;
        case CalculationResult::PERIOD_TYPE_HOUR:
            $result .= 'через ';
            $result .= ($calculationResult->getPeriodFrom() == 1) ? '' : $calculationResult->getPeriodFrom() . ' ';
            $result .= (new Declension('час', 'часа', 'часов'))->get($calculationResult->getPeriodFrom());
            break;
    }

    $result .= ', ' . CurrencyHelper::formatPrice($calculationResult->getPrice());

    return mb_strtolower($result);
}

/** @var OrderStorage $storage */
$storage = $arResult['STORAGE'];

/** @var CalculationResult[] $deliveries */
$deliveries = $arResult['DELIVERIES'];

$delivery = null;
$pickup = null;
foreach ($deliveries as $calculationResult) {
    $deliveryCode = $calculationResult->getData()['DELIVERY_CODE'];
    if (in_array($deliveryCode, DeliveryService::DELIVERY_CODES)) {
        $delivery = $calculationResult;
    } elseif (in_array($deliveryCode, DeliveryService::PICKUP_CODES)) {
        $pickup = $calculationResult;
    }
}

?>
<div class="b-container">
    <h1 class="b-title b-title--h1 b-title--order">Оформление заказа
    </h1>
    <div class="b-order js-order-whole-block">
        <div class="b-tab-list">
            <ul class="b-tab-list__list js-scroll-order">
                <li class="b-tab-list__item completed">
                    <a class="b-tab-list__link"
                       href="<?= $arParams['SEF_FOLDER'] ?>"
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
                        <div class="b-choice-recovery b-choice-recovery--order-step">
                            <?php if ($delivery) { ?>
                                <input class="b-choice-recovery__input js-recovery-telephone"
                                       id="order-delivery-address"
                                       type="radio"
                                       name="deliveryId"
                                       checked="checked"
                                       value="<?= $delivery->getData()['DELIVERY_ID'] ?>"/>
                                <label class="b-choice-recovery__label b-choice-recovery__label--left b-choice-recovery__label--order-step"
                                       for="order-delivery-address">
                                    <span class="b-choice-recovery__main-text">
                                        <span class="b-choice-recovery__second"><?= $delivery->getData(
                                            )['DELIVERY_NAME'] ?></span>
                                    </span>
                                    <span class="b-choice-recovery__addition-text">
                                        <?= showDeliveryTime($delivery) ?>
                                    </span>
                                    <span class="b-choice-recovery__addition-text b-choice-recovery__addition-text--mobile">
                                        <?= showDeliveryTime($delivery, true) ?>
                                </label>
                            <?php } ?>
                            <?php if ($pickup) { ?>
                                <input class="b-choice-recovery__input js-recovery-email js-myself-shop"
                                       id="order-delivery-pick-up"
                                       type="radio"
                                       name="deliveryId"
                                       value="<?= $pickup->getData()['DELIVERY_ID'] ?>"/>
                                <label class="b-choice-recovery__label b-choice-recovery__label--right b-choice-recovery__label--order-step js-open-popup"
                                       for="order-delivery-pick-up"
                                       data-popup-id="popup-order-stores">
                                    <span class="b-choice-recovery__main-text"><?= $pickup->getData(
                                        )['DELIVERY_NAME'] ?></span>
                                    <span class="b-choice-recovery__addition-text">
                                        <?= showDeliveryTime($pickup) ?>
                                    </span>
                                    <span class="b-choice-recovery__addition-text b-choice-recovery__addition-text--mobile">
                                        <?= showDeliveryTime($pickup, true) ?>
                                    </span>
                                </label>
                            <?php } ?>
                        </div>
                        <ul class="b-radio-tab">
                            <? if ($delivery) { ?>
                                <li class="b-radio-tab__tab js-telephone-recovery">
                                    <?php include 'include/delivery.php' ?>
                                </li>
                            <?php } ?>
                            <?php if ($pickup) { ?>
                                <li class="b-radio-tab__tab js-email-recovery">
                                    <div class="b-input-line b-input-line--address b-input-line--myself">
                                        <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Адрес доставки</span>
                                        </div>
                                        <ul class="b-delivery-list">
                                            <li class="b-delivery-list__item b-delivery-list__item--myself">
                                            <span class="b-delivery-list__link b-delivery-list__link--myself"><span
                                                        class="b-delivery-list__col b-delivery-list__col--color b-delivery-list__col--grey"></span>м. Улица Академика Янгеля, ул. Чертановская, д. 63/2, Москва</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="b-input-line b-input-line--myself">
                                        <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Время работы</span>
                                        </div>
                                        <div class="b-input-line__text-line b-input-line__text-line--myself">пн&mdash;пт:
                                            09:00&ndash;21:00, сб: 10:00&ndash;21:00, вс: 10:00&ndash;20:00
                                        </div>
                                    </div>
                                    <div class="b-input-line b-input-line--myself">
                                        <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Оплата в магазине</span>
                                        </div>
                                        <div class="b-input-line__text-line"><span class="b-input-line__pay-type"><span
                                                        class="b-icon b-icon--icon-cash">
                            <svg class="b-icon__svg" viewBox="0 0 16 12 " width="16px" height="12px">
                              <use class="b-icon__use" xlink:href="icons.svg#icon-cash">
                              </use>
                            </svg></span>наличными</span><span class="b-input-line__pay-type"> <span class="b-icon b-icon--icon-bank">
                            <svg class="b-icon__svg" viewBox="0 0 16 12 " width="16px" height="12px">
                              <use class="b-icon__use" xlink:href="icons.svg#icon-bank-card">
                              </use>
                            </svg></span>банковской картой</span>
                                        </div>
                                    </div>
                                    <div class="b-input-line b-input-line--partially">
                                        <div class="b-input-line__label-wrapper b-input-line__label-wrapper--order-full">
                                            <span class="b-input-line__label">Заказ в наличии частично</span>
                                        </div>
                                        <div class="b-radio b-radio--tablet-big"><input class="b-radio__input"
                                                                                        type="radio"
                                                                                        name="order-pick-time"
                                                                                        id="order-pick-time-now"
                                                                                        checked="checked"/>
                                            <label class="b-radio__label b-radio__label--tablet-big"
                                                   for="order-pick-time-now">
                                            </label>
                                            <div class="b-order-list b-order-list--myself">
                                                <ul class="b-order-list__list">
                                                    <li class="b-order-list__item b-order-list__item--myself">
                                                        <div class="b-order-list__order-text b-order-list__order-text--myself">
                                                            <div class="b-order-list__clipped-text">
                                                                <div class="b-order-list__text-backed">Забрать через час
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="b-order-list__order-value b-order-list__order-value--myself">
                                                            4 703 ₽
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="b-radio__addition-text">
                                                <p>За исключением:</p>
                                                <ol>
                                                    <li>Корм для кошек Хиллс Тунец стерилайз, меш. 8 кг</li>
                                                    <li>Фурминатор для больших кошек короткошерстных пород 7см</li>
                                                    <li>Moderna Туалет-домик для кошек 50см Friends forever синий</li>
                                                    <li>Petmax Игрушка для кошек Мыши с перьями 7 см (2 шт)</li>
                                                </ol>
                                            </div>
                                        </div>
                                        <div class="b-radio b-radio--tablet-big"><input class="b-radio__input"
                                                                                        type="radio"
                                                                                        name="order-pick-time"
                                                                                        id="order-pick-time-then"/>
                                            <label class="b-radio__label b-radio__label--tablet-big"
                                                   for="order-pick-time-then">
                                            </label>
                                            <div class="b-order-list b-order-list--myself">
                                                <ul class="b-order-list__list">
                                                    <li class="b-order-list__item b-order-list__item--myself">
                                                        <div class="b-order-list__order-text b-order-list__order-text--myself">
                                                            <div class="b-order-list__clipped-text">
                                                                <div class="b-order-list__text-backed">Забрать полный
                                                                    заказ
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="b-order-list__order-value b-order-list__order-value--myself">
                                                            13 269 ₽
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="b-radio__addition-text">
                                                <p>среда, 5 сентября в 15:00</p>
                                            </div>
                                        </div>
                                    </div>
                                    <a class="b-link b-link--another-point" href="javascript:void(0);" title="">Выбрать
                                        другой пункт самовывоза</a>
                                </li>
                            <?php } ?>
                        </ul>
                    </form>
                </article>
            </div>
            <?php include 'include/basket.php' ?>
        </div>
        <div class="b-order-list b-order-list--cost b-order-list--order-step-two js-order-next">
            <ul class="b-order-list__list b-order-list__list--cost">
                <li class="b-order-list__item b-order-list__item--cost b-order-list__item--order-step-two">
                    <div class="b-order-list__order-text b-order-list__order-text--order-step-two">
                        <div class="b-order-list__clipped-text">
                            <div class="b-order-list__text-backed">Товары с учетом всех скидок
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value b-order-list__order-value--order-step-two">13 269 ₽
                    </div>
                </li>
                <li class="b-order-list__item b-order-list__item--cost b-order-list__item--order-step-two">
                    <div class="b-order-list__order-text b-order-list__order-text--order-step-two">
                        <div class="b-order-list__clipped-text">
                            <div class="b-order-list__text-backed">Доставка
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value b-order-list__order-value--order-step-two">350 ₽
                    </div>
                </li>
                <li class="b-order-list__item b-order-list__item--cost b-order-list__item--order-step-two">
                    <div class="b-order-list__order-text b-order-list__order-text--order-step-two">
                        <div class="b-order-list__clipped-text">
                            <div class="b-order-list__text-backed">Итого к оплате
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value b-order-list__order-value--order-step-two">13 619 ₽
                    </div>
                </li>
            </ul>
        </div>
        <button class="b-button b-button--social b-button--next b-button--fixed-bottom js-order-next js-valid-out-sub">
            Далее
        </button>
    </div>
</div>
