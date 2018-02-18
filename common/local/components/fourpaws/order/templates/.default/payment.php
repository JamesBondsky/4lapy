<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Sale\BasketBase;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;
use FourPaws\App\Application;
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

/** @var CalculationResult $selectedDelivery */
$selectedDelivery = $arResult['SELECTED_DELIVERY'];

/** @var BasketBase $basket */
$basket = $arResult['BASKET'];

$isInnerDelivery = $deliveryService->isInnerDelivery($selectedDelivery) ||
    $deliveryService->isInnerPickup($selectedDelivery);

$selectedPayment = null;
foreach ($arResult['PAYMENTS'] as $payment) {
    if ((int)$payment['ID'] === $storage->getPaymentId()) {
        $selectedPayment = $payment;
    }
}

/**
 * @todo фикс цены. Нужен до тех пор, пока не реализовано разделение заказов
 */
$basketPrice = $basket->getPrice();
if ($deliveryService->isPickup($selectedDelivery) && $storage->isPartialGet()) {
    $basketPrice = $deliveryService->getStockResultByDelivery($selectedDelivery)
                                   ->filterByStore($arResult['SELECTED_SHOP'])
                                   ->getAvailable()
                                   ->getPrice();
}

$payments = $arResult['PAYMENTS'];

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
                            <?php /** @var array $payment */ ?>
                            <?php foreach ($payments as $payment) { ?>
                                <?php
                                if ((int)PaySystemManager::getInnerPaySystemId() === (int)$payment['ID']) {
                                    continue;
                                }

                                if ($isInnerDelivery && $payment['CODE'] === OrderService::PAYMENT_CASH) {
                                    $displayName = 'Наличными или картой при получении';
                                } else {
                                    $displayName = $payment['NAME'];
                                }

                                ?>
                                <input class="b-choice-recovery__input"
                                       id="order-payment-<?= $payment['ID'] ?>"
                                       type="radio"
                                       name="pay-type"
                                       data-pay="<?= $payment['CODE'] === OrderService::PAYMENT_ONLINE ? 'online' : 'cashe' ?>"
                                       value="<?= $payment['ID'] ?>"
                                    <?= (int)$payment['ID'] === $storage->getPaymentId() ? 'checked="checked"' : '' ?>/>
                                <label class="b-choice-recovery__label b-choice-recovery__label--left b-choice-recovery__label--order-step b-choice-recovery__label--radio-mobile"
                                       for="order-payment-<?= $payment['ID'] ?>">
                                    <span class="b-choice-recovery__main-text"><?= $displayName ?></span>
                                </label>
                                <?php
                            } ?>
                        </div>
                    </form>
                    <form class="b-order-contacts__form b-order-contacts__form--points js-form-validation success-valid"
                          action="/">
                        <?php if ($user->getDiscountCardNumber()) {
                            if ($arResult['MAX_BONUS_SUM']) {
                                ?>
                                <label class="b-order-contacts__label" for="point-pay">
                                    <b>Оплатить часть заказа бонусными баллами </b>
                                    (до <?= $arResult['MAX_BONUS_SUM'] ?>)
                                </label>
                                <div class="b-input b-input--order-line js-pointspay-input">
                                    <input class="b-input__input-field b-input__input-field--order-line js-pointspay-input js-only-number js-no-valid"
                                           id="point-pay"
                                           type="text"
                                           maxlength="5"
                                           size="5"
                                           value="<?= $storage->getBonusSum() ?>">
                                    <div class="b-error">
                                        <span class="js-message"></span>
                                    </div>
                                    <a class="b-input__close-points js-pointspay-close"
                                       href="javascript:void(0)"
                                       title=""
                                       style="display: none;">
                                    </a>
                                </div>
                                <button class="b-button b-button--order-line js-pointspay-button" style="">Подтвердить
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
                        <?php if ($storage->getBonusSum()) { ?>
                            <li class="b-order-list__item b-order-list__item--cost b-order-list__item--order-step-3">
                                <div class="b-order-list__order-text b-order-list__order-text--order-step-3">
                                    <div class="b-order-list__clipped-text">
                                        <div class="b-order-list__text-backed">
                                            Оплачено бонусами
                                        </div>
                                    </div>
                                </div>
                                <div class="b-order-list__order-value b-order-list__order-value--order-step-3">
                                    <?= CurrencyHelper::formatPrice($storage->getBonusSum(), false) ?>
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
                                    $basketPrice - $storage->getBonusSum() + $selectedDelivery->getPrice()
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
            Перейти к оплате
        </button>
    </div>
</div>
