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
                <li class="b-tab-list__item active js-active-order-step"><span class="b-tab-list__step">Шаг </span>2.
                    Выбор доставки
                </li>
                <li class="b-tab-list__item"><span class="b-tab-list__step">Шаг </span>3. Выбор оплаты
                </li>
                <li class="b-tab-list__item">Завершение
                </li>
            </ul>
        </div>
        <div class="b-order__block b-order__block--step-two">
            <div class="b-order__content js-order-content-block">
                <article class="b-order-contacts">
                    <header class="b-order-contacts__header">
                        <h2 class="b-title b-title--order-tab">Удобный для вас способ получения в
                        </h2><a class="b-link b-link--select b-link--order-step js-open-popup"
                                href="javascript:void(0);"
                                title="Москва"
                                data-popup-id="pick-city">г. Москва</a>
                    </header>
                    <form class="b-order-contacts__form b-order-contacts__form--choose-delivery js-form-validation"
                          data-url="/json/order-step-2.json"
                          id="order-step">
                        <div class="b-choice-recovery b-choice-recovery--order-step">
                            <input class="b-choice-recovery__input js-recovery-telephone"
                                   id="order-delivery-address"
                                   type="radio"
                                   name="order-delivery"
                                   checked="checked"/>
                            <label class="b-choice-recovery__label b-choice-recovery__label--left b-choice-recovery__label--order-step"
                                   for="order-delivery-address">
                                <span class="b-choice-recovery__main-text"> <span class="b-choice-recovery__first">Доставка </span><span
                                            class="b-choice-recovery__second">курьером</span></span><span class="b-choice-recovery__addition-text">суббота, 2 сентября, 350 ₽</span><span
                                        class="b-choice-recovery__addition-text b-choice-recovery__addition-text--mobile">сб, 2 сен, 350 ₽</span>
                            </label><input class="b-choice-recovery__input js-recovery-email js-myself-shop"
                                           id="order-delivery-pick-up"
                                           type="radio"
                                           name="order-delivery"/>
                            <label class="b-choice-recovery__label b-choice-recovery__label--right b-choice-recovery__label--order-step js-open-popup"
                                   for="order-delivery-pick-up"
                                   data-popup-id="popup-order-stores"><span class="b-choice-recovery__main-text">Самовывоз</span><span
                                        class="b-choice-recovery__addition-text">через час, бесплатно</span><span class="b-choice-recovery__addition-text b-choice-recovery__addition-text--mobile">через час, 0 ₽</span>
                            </label>
                        </div>
                        <ul class="b-radio-tab">
                            <li class="b-radio-tab__tab js-telephone-recovery">
                                <div class="b-input-line b-input-line--delivery-address-current js-hide-if-address">
                                    <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Адрес доставки</span>
                                    </div>
                                    <div class="b-radio b-radio--tablet-big"><input class="b-radio__input"
                                                                                    type="radio"
                                                                                    name="order-address"
                                                                                    id="order-address-1"
                                                                                    checked="checked"/>
                                        <label class="b-radio__label b-radio__label--tablet-big"
                                               for="order-address-1"><span class="b-radio__text-label">ул. Панфилова, д. 10, кв. 15, Москва</span>
                                        </label>
                                    </div>
                                    <div class="b-radio b-radio--tablet-big"><input class="b-radio__input"
                                                                                    type="radio"
                                                                                    name="order-address"
                                                                                    id="order-address-2"/>
                                        <label class="b-radio__label b-radio__label--tablet-big"
                                               for="order-address-2"><span class="b-radio__text-label">ул. Ленина, д. 4, кв. 24, под. 3, эт. 4, Москва</span>
                                        </label>
                                    </div>
                                    <div class="b-radio b-radio--tablet-big"><input class="b-radio__input"
                                                                                    type="radio"
                                                                                    name="order-address"
                                                                                    id="order-address-another"/>
                                        <label class="b-radio__label b-radio__label--tablet-big js-order-address-another"
                                               for="order-address-another"><span class="b-radio__text-label">Доставить по другому адресу…</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="b-radio-tab__new-address js-form-new-address js-hidden-valid-fields">
                                    <div class="b-input-line b-input-line--new-address">
                                        <div class="b-input-line__label-wrapper b-input-line__label-wrapper--back-arrow">
                                            <span class="b-input-line__label">Новый адрес доставки</span><a class="b-link b-link--back-arrow js-back-list-address"
                                                                                                            href="javascript:void(0);"
                                                                                                            title="Назад"><span
                                                        class="b-icon b-icon--back-long">
                              <svg class="b-icon__svg" viewBox="0 0 13 11 " width="13px" height="11px">
                                <use class="b-icon__use" xlink:href="icons.svg#icon-back-form">
                                </use>
                              </svg></span><span class="b-link__back-word">Вернуться </span><span class="b-link__mobile-word">к списку</span></a>
                                        </div>
                                    </div>
                                    <div class="b-input-line b-input-line--street">
                                        <div class="b-input-line__label-wrapper">
                                            <label class="b-input-line__label" for="order-address-street">Улица
                                            </label><span class="b-input-line__require">(обязательно)</span>
                                        </div>
                                        <div class="b-input b-input--registration-form">
                                            <input class="b-input__input-field b-input__input-field--registration-form"
                                                   type="text"
                                                   id="order-address-street"
                                                   placeholder=""
                                                   name="text"
                                                   data-url=""/>
                                            <div class="b-error"><span class="js-message"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="b-radio-tab__address-house">
                                        <div class="b-input-line b-input-line--house b-input-line--house-address">
                                            <div class="b-input-line__label-wrapper">
                                                <label class="b-input-line__label" for="order-address-house">Дом
                                                </label><span class="b-input-line__require">(обязательно)</span>
                                            </div>
                                            <div class="b-input b-input--registration-form">
                                                <input class="b-input__input-field b-input__input-field--registration-form"
                                                       type="text"
                                                       id="order-address-house"
                                                       placeholder=""
                                                       name="text"
                                                       data-url=""/>
                                                <div class="b-error"><span class="js-message"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--house">
                                            <div class="b-input-line__label-wrapper">
                                                <label class="b-input-line__label" for="order-address-part">Корпус
                                                </label>
                                            </div>
                                            <div class="b-input b-input--registration-form">
                                                <input class="b-input__input-field b-input__input-field--registration-form js-housing js-no-valid"
                                                       id="order-address-part"
                                                       type="text"/>
                                                <div class="b-error"><span class="js-message"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--house">
                                            <div class="b-input-line__label-wrapper">
                                                <label class="b-input-line__label" for="order-address-entrance">Подъезд
                                                </label>
                                            </div>
                                            <div class="b-input b-input--registration-form">
                                                <input class="b-input__input-field b-input__input-field--registration-form js-entrance js-no-valid"
                                                       id="order-address-entrance"
                                                       type="text"/>
                                                <div class="b-error"><span class="js-message"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--house">
                                            <div class="b-input-line__label-wrapper">
                                                <label class="b-input-line__label" for="order-address-floor">Этаж
                                                </label>
                                            </div>
                                            <div class="b-input b-input--registration-form">
                                                <input class="b-input__input-field b-input__input-field--registration-form js-floor js-no-valid"
                                                       id="order-address-floor"
                                                       type="text"/>
                                                <div class="b-error"><span class="js-message"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="b-input-line b-input-line--house">
                                            <div class="b-input-line__label-wrapper">
                                                <label class="b-input-line__label" for="order-address-apart">Кв., офис
                                                </label>
                                            </div>
                                            <div class="b-input b-input--registration-form">
                                                <input class="b-input__input-field b-input__input-field--registration-form js-office js-no-valid"
                                                       id="order-address-apart"
                                                       type="text"/>
                                                <div class="b-error"><span class="js-message"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="b-input-line b-input-line--desired-date">
                                    <div class="b-input-line__label-wrapper"><span class="b-input-line__label">Желаемая дата доставки</span>
                                    </div>
                                    <div class="b-select b-select--recall b-select--feedback-page">
                                        <select class="b-select__block b-select__block--recall b-select__block--feedback-page"
                                                name="order-date">
                                            <option value="" disabled="disabled" selected="selected">выберите
                                            </option>
                                            <option value="order-date-0">Суббота, 20.07.2017</option>
                                            <option value="order-date-1">Воскресенье, 21.07.2017</option>
                                            <option value="order-date-2">Понедельник, 22.07.2017</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="b-input-line b-input-line--interval">
                                    <div class="b-input-line__label-wrapper b-input-line__label-wrapper--interval"><span
                                                class="b-input-line__label">интервал</span>
                                    </div>
                                    <div class="b-select b-select--recall b-select--feedback-page b-select--interval">
                                        <select class="b-select__block b-select__block--recall b-select__block--feedback-page b-select__block--interval"
                                                name="order-interval">
                                            <option value="" disabled="disabled" selected="selected">выберите
                                            </option>
                                            <option value="order-interval-0">10:00—20:00</option>
                                            <option value="order-interval-1">10:00—12:00</option>
                                            <option value="order-interval-2">12:00—20:00</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="b-input-line b-input-line--textarea b-input-line--address-textarea">
                                    <div class="b-input-line__label-wrapper">
                                        <label class="b-input-line__label" for="order-comment">Комментарий к заказу
                                        </label>
                                    </div>
                                    <div class="b-input b-input--registration-form">
                                        <textarea class="b-input__input-field b-input__input-field--textarea b-input__input-field--registration-form"
                                                  id="order-comment"></textarea>
                                        <div class="b-error"><span class="js-message"></span>
                                        </div>
                                    </div>
                                </div>
                            </li>
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
                                                            <div class="b-order-list__text-backed">Забрать полный заказ
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
                        </ul>
                    </form>
                </article>
            </div>
            <aside class="b-order__list">
                <h4 class="b-title b-title--order-list js-popup-mobile-link"><span class="js-mobile-title-order">Заказ: 14 товаров</span>
                    (16 кг) на сумму 13 269 ₽
                </h4>
                <div class="b-order-list b-order-list--aside js-popup-mobile">
                    <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о
                        заказе</a>
                    <ul class="b-order-list__list js-order-list-block">
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Moderna Миска двойная пластиковая для кошек
                                        2*350 мл wildl болльшой большой текст
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">399 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Mealfeel консервы для кошек с домашней
                                        птицей, 100 г
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">599 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Домоседы Антицарапки (желтые)
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">377 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Petmax Носки черные с якорем разм. L
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">897 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Петмакс Игрушка для кошки Шар сизалевый с
                                        игрушкой, 11,5 с…
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">419 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Петмакс Игрушка для кошек Мячик сизалевый 5
                                        см
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">119 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Murmix лакомство для кошек снеки с лососем,
                                        уп. 50 г
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">890 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">АВЗ Шампунь FRUTTY CAT для кошек Сочный
                                        грейпфрут 250 м…
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">890 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Концентрированный кондиционер Жизненный
                                        кератин Artero …
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">890 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Корм для кошек Хиллс Тунец стерилайз, меш. 8
                                        кг
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">3 556 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Фурминатор для больших кошек короткошерстных
                                        пород 7см
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">2 012 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Moderna Туалет-домик для кошек 50см Friends
                                        forever синий
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">2 699 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Petmax Игрушка для кошек Мыши с перьями 7 см
                                        (2 шт)
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">299 ₽
                            </div>
                        </li>
                    </ul>
                </div>
                <h4 class="b-title b-title--order-list js-popup-mobile-link"><span class="js-mobile-title-order">Останется в корзине: 5</span>
                    товаров (4 кг) на сумму 8 566 ₽
                </h4>
                <div class="b-order-list b-order-list--aside js-popup-mobile">
                    <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о
                        заказе</a>
                    <ul class="b-order-list__list js-order-list-block">
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Корм для кошек Хиллс Тунец стерилайз, меш. 8
                                        кг
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">3 556 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Фурминатор для больших кошек короткошерстных
                                        пород 7см
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">2 012 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Moderna Туалет-домик для кошек 50см Friends
                                        forever синий
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">2 669 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item b-order-list__item--aside">
                            <div class="b-order-list__order-text b-order-list__order-text--aside">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Petmax Игрушка для кошек Мыши с перьями 7 см
                                        (2 шт)
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value b-order-list__order-value--aside">299 ₽
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="b-order__link-wrapper"><a class="b-link b-link--order-gotobusket b-link--order-gotobusket"
                                                      href="javascript:void(0)"
                                                      title="Вернуться в корзину"><span class="b-icon b-icon--order-busket">
                  <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                    <use class="b-icon__use" xlink:href="icons.svg#icon-reason">
                    </use>
                  </svg></span><span class="b-link__text b-link__text--order-gotobusket">Вернуться в корзину</span></a>
                </div>
            </aside>
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
