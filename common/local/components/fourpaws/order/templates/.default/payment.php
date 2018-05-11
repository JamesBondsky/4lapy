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
$deliveryPrice = $selectedDelivery->getPrice();
if (!empty($arResult['SPLIT_RESULT'])) {
    $deliveryPrice = $arResult['SPLIT_RESULT']['1']['DELIVERY']->getPrice();
}

/** @var BasketBase $basket */
$basket = $arResult['BASKET'];

$isInnerDelivery = $deliveryService->isInnerDelivery($selectedDelivery) ||
    $deliveryService->isInnerPickup($selectedDelivery);

$selectedPayment = null;
/** @var array $payments */
$payments = $arResult['PAYMENTS'];
$selectedPayment = current(array_filter($payments, function ($item) {
    return $item['CODE'] === OrderService::PAYMENT_CASH_OR_CARD;
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
                                    <span class="b-choice-recovery__main-text"><?= $payment['NAME'] ?></span>
                                </label>
                                <?php
                                $i++;
                            } ?>
                        </div>
                    </form>
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
                                       data-max-value="<?= $arResult['MAX_BONUS_SUM'] ?>"
                                       value="<?= min($storage->getBonus(), $arResult['MAX_BONUS_SUM']) ?>">
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
                        <div class="b-new-bonus-card_block">
                            <div class="b-new-bonus-card--step1<?= $storage->getDiscountCardNumber() ? ' hidden' : '' ?>">
                                <div class="b-new-bonus-card">
                                    <p class="js-new-bonus-card">Укажите бонусную карту</p><span>Для зачисления баллов</span>
                                </div>
                            </div>
                            <div class="b-new-bonus-card--step2 hidden">
                                <span class="title">Номер бонусной карты</span>
                                <span class="js-new-card-cancel"></span>
                                <form class="b-account-bonus-card__form js-form-validation js-offers-query success-valid"
                                      data-url="<?= $arResult['URL']['BONUS_CARD_VALIDATION'] ?>" method="post">
                                    <div class="b-order-contacts__link b-order-contacts__link--hidden js-number-input">
                                        <div class="b-input b-input--account-bonus js-offers">
                                            <input class="b-input__input-field b-input__input-field--account-bonus js-offers ok"
                                                   value="<?= $storage->getDiscountCardNumber() ?>"
                                                   type="text" id="bonus" placeholder="" name="text" data-url="">
                                            <div class="b-error b-error--ok">
                                                <span class="js-message">Поле верно заполнено</span>
                                            </div>
                                        </div>
                                        <button class="b-order-contacts__button">Привязать</button>
                                    </div>
                                </form>
                            </div>
                            <div class="b-new-bonus-card--step3 <?= !$storage->getDiscountCardNumber() ? ' hidden' : '' ?>">
                                <div class="b-new-bonus-card--info">
                                    <p>Бонусная карта для зачисления баллов: <span><?= $storage->getDiscountCardNumber() ?></span></p>
                                    <span class="js-another-bonus-card">Указать другую карту</span>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
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
                            <div class="b-order-list__order-value b-order-list__order-value--order-step-3"
                                 data-cost="<?= $basketPrice ?>">
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
                            <div class="b-order-list__order-value b-order-list__order-value--order-step-3"
                                 data-cost="<?= $deliveryPrice ?>">
                                <?= CurrencyHelper::formatPrice($deliveryPrice, false) ?>
                            </div>
                        </li>
                        <?php if ($storage->getBonus()) { ?>
                                <li class="b-order-list__item b-order-list__item--cost b-order-list__item--order-step-3 b-order-list__pointspay">
                                        <div class="b-order-list__order-text b-order-list__order-text--order-step-3">
                                           <div class="b-order-list__clipped-text">
                                                   <div class="b-order-list__text-backed">
                                                           Оплачено бонусами
                                                       </div>
                                               </div>
                                       </div>
                                   <div class="b-order-list__order-value b-order-list__order-value--order-step-3">
                                           <?= CurrencyHelper::formatPrice(max($storage->getBonus(), $arResult['MAX_BONUS_SUM']), false) ?>
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
                                    $basketPrice - $storage->getBonus() + $deliveryPrice
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
