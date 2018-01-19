<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="b-container">
    <h1 class="b-title b-title--h1 b-title--order">Оформление заказа
    </h1>
    <div class="b-order js-order-whole-block">
        <div class="b-tab-list">
            <ul class="b-tab-list__list js-scroll-order">
                <li class="b-tab-list__item completed"><a class="b-tab-list__link"
                                                          href="javascript:void(0);"
                                                          title=""><span class="b-tab-list__step">Шаг </span>1.
                        Контактные данные</a>
                </li>
                <li class="b-tab-list__item completed"><a class="b-tab-list__link"
                                                          href="javascript:void(0);"
                                                          title=""><span class="b-tab-list__step">Шаг </span>2. Выбор
                        доставки</a>
                </li>
                <li class="b-tab-list__item active js-active-order-step"><span class="b-tab-list__step">Шаг </span>3.
                    Выбор оплаты
                </li>
                <li class="b-tab-list__item">Завершение
                </li>
            </ul>
        </div>
        <div class="b-order__block b-order__block--no-flex b-order__block--no-border">
            <div class="b-order__content b-order__content--no-border b-order__content--step-3">
                <article class="b-order-contacts">
                    <header class="b-order-contacts__header">
                        <h2 class="b-title b-title--order-tab">Как вы будете оплачивать
                        </h2>
                    </header>
                    <form class="b-order-contacts__form b-order-contacts__form--points-top js-form-validation">
                        <div class="b-choice-recovery b-choice-recovery--flex"><input class="b-choice-recovery__input"
                                                                                      id="order-delivery-address"
                                                                                      type="radio"
                                                                                      name="order-delivery"
                                                                                      checked="checked"/>
                            <label class="b-choice-recovery__label b-choice-recovery__label--left b-choice-recovery__label--order-step b-choice-recovery__label--radio-mobile"
                                   for="order-delivery-address"><span class="b-choice-recovery__main-text">Онлайн оплата</span>
                            </label><input class="b-choice-recovery__input"
                                           id="order-delivery-pick-up"
                                           type="radio"
                                           name="order-delivery"/>
                            <label class="b-choice-recovery__label b-choice-recovery__label--right b-choice-recovery__label--order-step b-choice-recovery__label--radio-mobile"
                                   for="order-delivery-pick-up"
                                   data-popup-id="popup-order-stores"><span class="b-choice-recovery__main-text">Наличными или картой</span><span
                                        class="b-choice-recovery__main-text">при&nbsp;получении</span>
                            </label>
                        </div>
                    </form>
                    <form class="b-order-contacts__form b-order-contacts__form--points js-form-validation"
                          action="/"
                          data-url="/json/order-step-3.json"
                          id="order-step">
                        <label class="b-order-contacts__label" for="point-pay"><b>Оплатить часть заказа бонусными
                                баллами </b>(до 299)
                        </label>
                        <div class="b-input b-input--order-line js-pointspay-input">
                            <input class="b-input__input-field b-input__input-field--order-line js-pointspay-input js-no-valid"
                                   id="point-pay"
                                   type="text"/>
                            <div class="b-error"><span class="js-message"></span>
                            </div>
                            <a class="b-input__close-points js-pointspay-close" href="javascript:void(0)" title=""></a>
                        </div>
                        <button class="b-button b-button--order-line js-pointspay-button">Подтвердить
                        </button>
                    </form>
                </article>
            </div>
            <hr class="b-hr b-hr--order-step-3"/>
            <div class="b-order__content b-order__content--no-border b-order__content--no-padding b-order__content--step-3">
                <div class="b-order-list b-order-list--cost b-order-list--order-step-3">
                    <ul class="b-order-list__list b-order-list__list--cost">
                        <li class="b-order-list__item b-order-list__item--cost b-order-list__item--order-step-3">
                            <div class="b-order-list__order-text b-order-list__order-text--order-step-3">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Товары с учетом всех скидок
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--order-step-3">4 703 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--cost b-order-list__item--order-step-3">
                            <div class="b-order-list__order-text b-order-list__order-text--order-step-3">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Доставка
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--order-step-3">350 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--cost b-order-list__item--order-step-3">
                            <div class="b-order-list__order-text b-order-list__order-text--order-step-3">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Итого к оплате
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--order-step-3">5 053 ₽
                            </div>
                        </li>
                    </ul>
                </div>
                <button class="b-button b-button--order-step-3 b-button--next b-button--fixed-bottom js-order-next js-valid-out-sub">
                    Перейти к оплате
                </button>
                <div class="b-order__text-block b-order__text-block--additional">
                    <p>Оформляя заказ я даю своё согласие на обработку персональных данных и подтверждаю ознакомление с
                        договором офертой.</p>
                    <p>В соответствии с ФЗ №54-ФЗ кассовый чек при онлайн-оплате на сайте будет предоставлен в
                        электронном виде на указанный при оформлении заказа номер телефона или email.</p>
                </div>
            </div>
        </div>
    </div>
</div>
