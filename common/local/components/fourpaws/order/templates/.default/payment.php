<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Sale\BasketBase;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\UserBundle\Entity\User;

/**
 * @var array $arResult
 * @var array $arParams
 * @var CMain $APPLICATION
 */

/** @var DeliveryService $deliveryService */
$deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
/** @var OrderStorage $storage */
$storage = $arResult['STORAGE'];

/** @var CalculationResultInterface $selectedDelivery */
$selectedDelivery = $arResult['SELECTED_DELIVERY'];
if (!empty($arResult['SPLIT_RESULT'])) {
    $selectedDelivery = $arResult['SPLIT_RESULT']['1']['DELIVERY'];
}

/** @var BasketBase $basket */
$basket = $arResult['BASKET'];

$isInnerDelivery = $deliveryService->isInnerDelivery($selectedDelivery) ||
    $deliveryService->isInnerPickup($selectedDelivery);

$selectedPayment = null;
/** @var array $payments */
$payments = $arResult['PAYMENTS'];
$selectedPayment = current(array_filter($payments, function($item) {
    return $item['CODE'] === OrderService::PAYMENT_CASH;
}));

foreach ($payments as $i => $payment) {
    if ((int)PaySystemManager::getInnerPaySystemId() === (int)$payment['ID']) {
       unset($payments[$i]);
    }
    if ((int)$payment['ID'] === $storage->getPaymentId()) {
        $selectedPayment = $payment;
    }
}
if (!$selectedPayment) {
    $selectedPayment = current($payments);
}

$basketPrice = $selectedDelivery->getStockResult()->getPrice();
if ($arResult['PARTIAL_PICKUP_AVAILABLE'] && $storage->isSplit()) {
    $basketPrice = $arResult['PARTIAL_PICKUP']->getStockResult()->getPrice();
}

/** @var User $user */
$user = $arResult['USER'];
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
                        <span class="b-tab-list__step">Шаг</span>
                        1. Контактные данные
                    </a>
                </li>
                <li class="b-tab-list__item completed">
                    <a class="b-tab-list__link"
                       href="<?= $arResult['URL']['DELIVERY'] ?>"
                       title="">
                        <span class="b-tab-list__step">Шаг</span>
                        2. Выбор доставки
                    </a>
                </li>
                <li class="b-tab-list__item active js-active-order-step">
                    <span class="b-tab-list__step">Шаг</span>
                    3. Выбор оплаты
                </li>
                <li class="b-tab-list__item">
                    Завершение
                </li>
            </ul>
        </div>
        <div class="b-order__block b-order__block--no-flex b-order__block--no-border">
            <div class="b-order__content b-order__content--no-border b-order__content--step-3">
                <article class="b-order-contacts">
                    <header class="b-order-contacts__header">
                        <h2 class="b-title b-title--order-tab">
                            Как вы будете оплачивать
                        </h2>
                    </header>
                    <form class="b-order-contacts__form b-order-contacts__form--points-top js-form-validation"
                          method="post"
                          data-url="<?= $arResult['URL']['PAYMENT_VALIDATION'] ?>"
                          id="order-step">
                        <div class="b-choice-recovery b-choice-recovery--flex">
                            <?php /** @var array $payment */
                            $i = 0;
                            $max = count($payments);
                            foreach ($payments as $payment) {
                                if ($isInnerDelivery && $payment['CODE'] === OrderService::PAYMENT_CASH) {
                                    $displayName = 'Наличными или картой при получении';
                                } else {
                                    $displayName = $payment['NAME'];
                                }
                                $labelClass = $i % 2 !== 0
                                    ? ' b-choice-recovery__label--right'
                                    : ' b-choice-recovery__label--left';
                                if ($i === $max - 1) {
                                    $labelClass .= ' b-choice-recovery__label--right';
                                }
                                ?>
                                <input class="b-choice-recovery__input"
                                       id="order-payment-<?= $payment['ID'] ?>"
                                       type="radio"
                                       name="pay-type"
                                       data-pay="<?= $payment['CODE'] === OrderService::PAYMENT_ONLINE ? 'online' : 'cashe' ?>"
                                       value="<?= $payment['ID'] ?>"
                                    <?= (int)$payment['ID'] === (int)$selectedPayment['ID'] ? 'checked="checked"' : '' ?>/>
                                <label class="b-choice-recovery__label<?= $labelClass ?> b-choice-recovery__label--order-step b-choice-recovery__label--radio-mobile"
                                       for="order-payment-<?= $payment['ID'] ?>">
                                    <span class="b-choice-recovery__main-text"><?= $displayName ?></span>
                                </label>
                                <?php
                                $i++;
                            } ?>
                        </div>
                    </form>
                    <form class="b-order-contacts__form b-order-contacts__form--points js-form-validation success-valid"
                          action="/">
                        <?php if ($user && $user->getDiscountCardNumber()) {
                            if ($arResult['MAX_BONUS_SUM']) {
                                $active = $storage->getBonus() > 0;
                                ?>
                                <label class="b-order-contacts__label" for="point-pay">
                                    <b>Оплатить часть заказа бонусными баллами </b>
                                    (до <?= $arResult['MAX_BONUS_SUM'] ?>)
                                </label>
                                <div class="b-input b-input--order-line js-pointspay-input<?= $active ? ' active' : '' ?>">
                                    <input class="b-input__input-field b-input__input-field--order-line js-pointspay-input js-only-number js-no-valid"
                                           id="point-pay"
                                           type="text"
                                           maxlength="5"
                                           size="5"
                                           value="<?= $storage->getBonus() ?>">
                                    <div class="b-error">
                                        <span class="js-message"></span>
                                    </div>
                                    <a class="b-input__close-points js-pointspay-close<?= $active ? ' active' : '' ?>"
                                       href="javascript:void(0)"
                                       title=""
                                        <?= $active ? 'style="display:inline"' : '' ?>>
                                    </a>
                                </div>
                                <button class="b-button b-button--order-line js-pointspay-button<?= $active ? ' hide' : '' ?>"
                                    <?= $active ? 'style="display:none"' : '' ?>>
                                    Подтвердить
                                </button>
                            <?php } ?>
                        <?php } else { ?>
                            <?php /* @todo форма ввода номера бонусной карты - верстки нет */ ?>
                        <?php } ?>
                    </form>
                </article>
            </div>
            <hr class="b-hr b-hr--order-step-3">
            <div class="b-order__content b-order__content--no-border b-order__content--no-padding b-order__content--step-3">
                <div class="b-order-list b-order-list--cost b-order-list--order-step-3">
                    <ul class="b-order-list__list b-order-list__list--cost">
                        <li class="b-order-list__item b-order-list__item--cost b-order-list__item--order-step-3">
                            <div class="b-order-list__order-text b-order-list__order-text--order-step-3">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">
                                        Товары с учетом всех скидок
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--order-step-3">
                                <?= CurrencyHelper::formatPrice($basketPrice, false) ?>
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--cost b-order-list__item--order-step-3">
                            <div class="b-order-list__order-text b-order-list__order-text--order-step-3">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">
                                        Доставка
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--order-step-3">
                                <?= CurrencyHelper::formatPrice($selectedDelivery->getPrice(), false) ?>
                            </div>
                        </li>
                        <?php if ($storage->getBonus()) { ?>
                            <li class="b-order-list__item b-order-list__item--cost b-order-list__item--order-step-3">
                                <div class="b-order-list__order-text b-order-list__order-text--order-step-3">
                                    <div class="b-order-list__clipped-text">
                                        <div class="b-order-list__text-backed">
                                            Оплачено бонусами
                                        </div>
                                    </div>
                                </div>
                                <div class="b-order-list__order-value b-order-list__order-value--order-step-3">
                                    <?= CurrencyHelper::formatPrice($storage->getBonus(), false) ?>
                                </div>
                            </li>
                        <?php } ?>
                        <li class="b-order-list__item b-order-list__item--cost b-order-list__item--order-step-3">
                            <div class="b-order-list__order-text b-order-list__order-text--order-step-3">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">
                                        Итого к оплате
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--order-step-3">
                                <?= CurrencyHelper::formatPrice(
                                    $basketPrice - $storage->getBonus() + $selectedDelivery->getPrice()
                                ) ?>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="b-order__text-block b-order__text-block--additional">
                    <p>Оформляя заказ, я даю своё согласие на обработку персональных данных и подтверждаю ознакомление с
                        договором-офертой.</p>
                    <p>В соответствии с ФЗ №54-ФЗ кассовый чек при онлайн-оплате на сайте будет предоставлен в
                        электронном виде на указанный при оформлении заказа номер телефона или email.</p>
                </div>
            </div>
        </div>
        <button class="b-button b-button--order-step-3 b-button--next b-button--fixed-bottom js-order-next js-order-step-3-submit">
            <?php if ($selectedPayment['CODE'] === OrderService::PAYMENT_ONLINE) { ?>
                Перейти к оплате
            <?php } else { ?>
                Заказать
            <?php } ?>
        </button>
    </div>
</div>
