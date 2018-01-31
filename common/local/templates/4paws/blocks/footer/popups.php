<?php

use Bitrix\Main\Application;
use FourPaws\App\MainTemplate;
use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var MainTemplate $template */
/** @noinspection PhpUnhandledExceptionInspection */
$template = MainTemplate::getInstance(Application::getInstance()->getContext()); ?>
<div class="b-popup-wrapper js-popup-wrapper">
    <?php $APPLICATION->IncludeComponent('fourpaws:city.selector', 'popup', [], false, ['HIDE_ICONS' => 'Y']);
    $APPLICATION->IncludeComponent('fourpaws:auth.form', 'popup', [], false, ['HIDE_ICONS' => 'Y']); ?>
    <section class="b-popup-pick-city b-popup-pick-city--subscribe-delivery js-popup-section"
             data-popup="subscribe-delivery">
        <a class="b-popup-pick-city__close b-popup-pick-city__close--subscribe-delivery js-close-popup"
           href="javascript:void(0);"
           title="Закрыть"></a>
        <div class="b-registration b-registration--subscribe-delivery">
            <header class="b-registration__header">
                <h1 class="b-title b-title--h1 b-title--registration">Подписка на доставку</h1>
            </header>
            <form class="b-registration__form js-form-validation">
                <label class="b-registration__label b-registration__label--subscribe-delivery">День первой
                                                                                               доставки</label>
                <div class="b-select b-select--subscribe-delivery">
                    <select class="b-select__block b-select__block--subscribe-delivery" name="first-delivery">
                        <option value="first-delivery-0">10:00—16:00</option>
                        <option value="first-delivery-1">60:00—18:00</option>
                        <option value="first-delivery-2">18:00—20:00</option>
                    </select>
                </div>
                <label class="b-registration__label b-registration__label--subscribe-delivery">Интервал</label>
                <div class="b-select b-select--subscribe-delivery">
                    <select class="b-select__block b-select__block--subscribe-delivery" name="delivery-interval">
                        <option value="delivery-interval-0">10:00—16:00</option>
                        <option value="delivery-interval-1">60:00—18:00</option>
                        <option value="delivery-interval-2">18:00—20:00</option>
                    </select>
                </div>
                <label class="b-registration__label b-registration__label--subscribe-delivery">Как часто</label>
                <div class="b-select b-select--subscribe-delivery">
                    <select class="b-select__block b-select__block--subscribe-delivery" name="frequency-delivery">
                        <option value="frequency-delivery-0">10:00—16:00</option>
                        <option value="frequency-delivery-1">60:00—18:00</option>
                        <option value="frequency-delivery-2">18:00—20:00</option>
                    </select>
                </div>
                <div class="b-registration__text b-registration__text--subscribe-delivery">Периодичность, день и время
                                                                                           доставки вы сможете поменять
                                                                                           в личном кабинете в любой
                                                                                           момент
                </div>
                <ul class="b-registration__info-delivery">
                    <li class="b-registration__item-delivery">
                        <span class="b-icon b-icon--delivery-calendar">
                            <?= new SvgDecorator('icon-delivery-calendar', 16, 17) ?>
                        </span>
                        <div class="b-registration__text b-registration__text--info-delivery">
                            <p>Параметры подписки: по субботам, раз в неделю, с 10 до 20.</p>
                            <p>Первая доставка: суббота 20.07.2017 с 10 до 20</p>
                        </div>
                    </li>
                    <li class="b-registration__item-delivery">
                        <span class="b-icon b-icon--delivery-calendar">
                            <?= new SvgDecorator('icon-delivery-car', 18, 12) ?>
                        </span>
                        <div class="b-registration__text b-registration__text--info-delivery">
                            <p>Доставка курьером, по адресу:</p>
                            <p>г. Москва, ул. Ленина, д. 4, кв. 24, под. 3, эт. 4</p>
                        </div>
                    </li>
                    <li class="b-registration__item-delivery">
                        <span class="b-icon b-icon--delivery-calendar">
                            <?= new SvgDecorator('icon-delivery-dollar', 18, 14) ?>
                        </span>
                        <div class="b-registration__text b-registration__text--info-delivery">
                            <p>Оплата: наличными или картой при получении.</p>
                        </div>
                    </li>
                </ul>
                <button class="b-button b-button--subscribe-delivery">Сохранить</button>
            </form>
        </div>
    </section>
    <?php if ($template->hasPersonalReferral()) {
        $APPLICATION->IncludeComponent('fourpaws:personal.referral', 'popup', [], $component, ['HIDE_ICONS' => 'Y']);
    }
    if ($template->hasPersonalAddress()) {
        $APPLICATION->IncludeComponent('fourpaws:personal.address', 'popup', [], $component, ['HIDE_ICONS' => 'Y']);
    }
    if ($template->hasPersonalPet()) {
        $APPLICATION->IncludeComponent('fourpaws:personal.pets', 'popup', [], $component, ['HIDE_ICONS' => 'Y']);
    }
    if ($template->hasPersonalProfile()) {
        $APPLICATION->IncludeComponent('fourpaws:personal.profile', 'popupChangePassword', [], $component,
                                       ['HIDE_ICONS' => 'Y']);
        $APPLICATION->IncludeComponent('fourpaws:personal.profile', 'popupChangeData', [], $component,
                                       ['HIDE_ICONS' => 'Y']);
        $APPLICATION->IncludeComponent('fourpaws:personal.profile', 'popupChangePhone', [], $component,
                                       ['HIDE_ICONS' => 'Y']);
    } ?>
    <?php include 'tmp_gifts_popup.php' ?>
    <?php include 'modal_popup.php' ?>
</div>
