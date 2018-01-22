<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\SaleBundle\Entity\OrderStorage;

/**
 * @var array $arParams
 * @var array $arResult
 */

/**
 * @var OrderStorage $storage
 */
$storage = $arResult['STORAGE'];

?>
<div class="b-container">
    <h1 class="b-title b-title--h1 b-title--order">Оформление заказа
    </h1>
    <div class="b-order js-order-whole-block">
        <div class="b-tab-list">
            <ul class="b-tab-list__list">
                <li class="b-tab-list__item active"><span class="b-tab-list__step">Шаг </span>1. Контактные данные
                </li>
                <li class="b-tab-list__item"><span class="b-tab-list__step">Шаг </span>2. Выбор доставки
                </li>
                <li class="b-tab-list__item"><span class="b-tab-list__step">Шаг </span>3. Выбор оплаты
                </li>
                <li class="b-tab-list__item">Завершение
                </li>
            </ul>
        </div>
        <div class="b-order__block">
            <div class="b-order__content js-order-content-block">
                <article class="b-order-contacts">
                    <header class="b-order-contacts__header">
                        <h2 class="b-title b-title--order-tab">Контактные данные для оформления
                        </h2>
                    </header>
                    <form class="b-order-contacts__form js-form-validation"
                          id="order-step"
                          data-url="<?= $arResult['URL']['AUTH_VALIDATION'] ?>">
                        <div class="b-input-line">
                            <div class="b-input-line__label-wrapper">
                                <label class="b-input-line__label" for="order-name">Имя
                                </label><span class="b-input-line__require">(обязательно)</span>
                            </div>
                            <div class="b-input b-input--registration-form">
                                <input class="b-input__input-field b-input__input-field--registration-form"
                                       type="text"
                                       id="order-name"
                                       placeholder=""
                                       name="name"
                                       data-url=""
                                       value="<?= $storage->getName() ?>">
                                <div class="b-error"><span class="js-message"></span>
                                </div>
                            </div>
                        </div>
                        <div class="b-input-line">
                            <div class="b-input-line__label-wrapper js-information-comment">
                                <label class="b-input-line__label" for="order-phone">Мобильный телефон</label>
                                <span class="b-input-line__require">(обязательно)</span>
                                <a class="b-information-link b-information-link--input js-popover-information-open"
                                   href="javascript:void(0);"
                                   title="">
                                    <span class="b-information-link__icon">i</span>
                                    <div class="b-popover-information b-popover-information--input js-popover-information">
                                    </div>
                                </a>
                            </div>
                            <div class="b-input-line__comment-block js-comment-wrapper">
                                <div class="b-input b-input--registration-form js-this-comment-desktop">
                                    <input class="b-input__input-field b-input__input-field--registration-form js-this-comment-desktop"
                                           type="tel"
                                           id="order-phone"
                                           placeholder=""
                                           name="phone"
                                           data-url=""
                                           data-tel="0"
                                           value="<?= $storage->getPhone() ?>">
                                    <div class="b-error"><span class="js-message"></span>
                                    </div>
                                </div>
                                <span class="b-input-line__comment js-comment">Для проверки статуса заказов на сайте</span>
                            </div>
                        </div>
                        <div class="b-input-line">
                            <div class="b-input-line__label-wrapper js-information-comment">
                                <label class="b-input-line__label" for="order-email">Эл. почта
                                </label>
                                <a class="b-information-link b-information-link--input js-popover-information-open"
                                   href="javascript:void(0);"
                                   title=""> <span class="b-information-link__icon">i</span>
                                    <div class="b-popover-information b-popover-information--input js-popover-information">
                                    </div>
                                </a>
                            </div>
                            <div class="b-input-line__comment-block js-comment-wrapper">
                                <div class="b-input b-input--registration-form js-this-comment-desktop">
                                    <input class="b-input__input-field b-input__input-field--registration-form js-this-comment-desktop"
                                           type="email"
                                           id="order-email"
                                           placeholder=""
                                           name="email"
                                           data-url=""
                                           value="<?= $storage->getEmail() ?>">
                                    <div class="b-error">
                                        <span class="js-message"></span>
                                    </div>
                                </div>
                                <span class="b-input-line__comment js-comment">Для проверки статуса заказов и для рассылки новостей и акций</span>
                            </div>
                        </div>
                        <div class="b-order-contacts__add-layout">
                            <div class="b-order-contacts__link-block">
                                <a class="b-link b-link--add-phone js-order-add-phone-link"
                                   href="javascript:void(0);"
                                   title=""
                                    <?= $storage->getAltPhone() ? 'style="display:none"' : '' ?>>
                                    Дополнительный телефон
                                </a>
                                <a class="b-information-link b-information-link--additional-telephone-order js-additional-telephone js-popover-information-open"
                                   href="javascript:void(0);"
                                   title=""> <span class="b-information-link__icon">i</span>
                                    <div class="b-popover-information b-popover-information--additional-telephone-order js-popover-information">
                                    </div>
                                </a>
                                <span class="b-order-contacts__text js-additional-telephone-info">Если мы не дозвонимся по основному телефону</span>
                            </div>
                            <div class="b-order-contacts__layout js-order-add-phone js-hidden-valid-fields"
                                <?= $storage->getAltPhone() ? 'style="display:block"' : '' ?>>
                                <div class="b-input-line">
                                    <div class="b-input-line__label-wrapper js-information-comment">
                                        <label class="b-input-line__label" for="order-phone-dop">Дополнительный телефон
                                        </label>
                                        <a class="b-information-link b-information-link--input js-popover-information-open"
                                           href="javascript:void(0);"
                                           title=""> <span class="b-information-link__icon">i</span>
                                            <div class="b-popover-information b-popover-information--input js-popover-information">
                                            </div>
                                        </a>
                                    </div>
                                    <div class="b-input-line__comment-block js-comment-wrapper">
                                        <div class="b-input b-input--registration-form js-this-comment-desktop"><input
                                                    class="b-input__input-field b-input__input-field--registration-form js-this-comment-desktop"
                                                    type="tel"
                                                    id="order-phone-dop"
                                                    placeholder=""
                                                    name="PROPERTY_PHONE_ALT"
                                                    data-url=""
                                                    data-tel="1">
                                            <div class="b-error"><span class="js-message"></span>
                                            </div>
                                        </div>
                                        <span class="b-input-line__comment js-comment">Если мы не дозвонимся по основному телефону</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="b-input-line">
                            <div class="b-input-line__label-wrapper">
                                <span class="b-input-line__label">Как с вами связаться для подтверждения заказа</span>
                            </div>
                            <?php // @todo show enum values ?>
                            <div class="b-radio b-radio--tablet-big">
                                <input class="b-radio__input"
                                       type="radio"
                                       name="communicationWay"
                                       id="order-call"
                                       <?= $storage->getCommunicationWay() == '02' ? 'checked="checked"' : '' ?>
                                       data-radio="0"
                                       value="02">
                                <label class="b-radio__label b-radio__label--tablet-big"
                                       for="order-call">
                                    <span class="b-radio__text-label">Звонок оператора</span>
                                </label>
                            </div>
                            <div class="b-radio b-radio--tablet-big">
                                <input class="b-radio__input"
                                       type="radio"
                                       name="communicationWay"
                                       id="order-sms"
                                       <?= $storage->getCommunicationWay() == '01' ? 'checked="checked"' : '' ?>
                                       data-radio="1"
                                       value="01">
                                <label class="b-radio__label b-radio__label--tablet-big"
                                       for="order-sms"><span class="b-radio__text-label">SMS-сообщение</span>
                                </label>
                            </div>
                        </div>
                    </form>
                </article>
            </div>
            <aside class="b-order__list">
                <h4 class="b-title b-title--order-list js-popup-mobile-link">Заказ: 14 товаров (16 кг) на сумму 13 269 ₽
                </h4>
                <div class="b-order-list js-popup-mobile">
                    <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о
                        заказе</a>
                    <ul class="b-order-list__list js-order-list-block">
                        <li class="b-order-list__item">
                            <div class="b-order-list__order-text">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Moderna Миска двойная пластиковая для кошек
                                        2*350 мл wildl болльшой большой текст
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value">399 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item">
                            <div class="b-order-list__order-text">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Mealfeel консервы для кошек с домашней
                                        птицей, 100 г
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value">599 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item">
                            <div class="b-order-list__order-text">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Домоседы Антицарапки (желтые)
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value">377 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item">
                            <div class="b-order-list__order-text">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Petmax Носки черные с якорем разм. L
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value">897 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item">
                            <div class="b-order-list__order-text">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Петмакс Игрушка для кошки Шар сизалевый с
                                        игрушкой, 11,5 с…
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value">419 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item">
                            <div class="b-order-list__order-text">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Петмакс Игрушка для кошек Мячик сизалевый 5
                                        см
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value">119 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item">
                            <div class="b-order-list__order-text">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Murmix лакомство для кошек снеки с лососем,
                                        уп. 50 г
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value">890 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item">
                            <div class="b-order-list__order-text">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">АВЗ Шампунь FRUTTY CAT для кошек Сочный
                                        грейпфрут 250 м…
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value">890 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item">
                            <div class="b-order-list__order-text">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Концентрированный кондиционер Жизненный
                                        кератин Artero …
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value">890 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item">
                            <div class="b-order-list__order-text">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Корм для кошек Хиллс Тунец стерилайз, меш. 8
                                        кг
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value">3 556 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item">
                            <div class="b-order-list__order-text">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Фурминатор для больших кошек короткошерстных
                                        пород 7см
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value">2 012 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item">
                            <div class="b-order-list__order-text">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Moderna Туалет-домик для кошек 50см Friends
                                        forever синий
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value">2 699 ₽
                            </div>
                        </li>
                        <li class="b-order-list__item">
                            <div class="b-order-list__order-text">
                                <div class="b-order-list__clipped-text">
                                    <div class="b-order-list__text-backed">Petmax Игрушка для кошек Мыши с перьями 7 см
                                        (2 шт)
                                    </div>
                                </div>
                            </div>
                            <div class="b-order-list__order-value">299 ₽
                            </div>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>

        <button class="b-button b-button--social b-button--next b-button--fixed-bottom js-order-next js-valid-out-sub">
            Далее
        </button>
    </div>
</div>
