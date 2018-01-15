<?php

use Bitrix\Main\Application;
use FourPaws\App\MainTemplate;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var MainTemplate $template */
/** @noinspection PhpUnhandledExceptionInspection */
$template = MainTemplate::getInstance(Application::getInstance()->getContext()); ?>
<div class="b-popup-wrapper js-popup-wrapper">
    <?php $APPLICATION->IncludeComponent(
        'fourpaws:city.selector',
        'popup',
        [],
        false,
        ['HIDE_ICONS' => 'Y']
    );
    $APPLICATION->IncludeComponent(
        'fourpaws:auth.form',
        'popup',
        [],
        false,
        ['HIDE_ICONS' => 'Y']
    ); ?>
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
                    <li class="b-registration__item-delivery"><span class="b-icon b-icon--delivery-calendar"><svg class="b-icon__svg"
                                                                                                                  viewBox="0 0 16 17 "
                                                                                                                  width="16px"
                                                                                                                  height="17px"><use
                                        class="b-icon__use"
                                        xlink:href="icons.svg#icon-delivery-calendar"></use></svg></span>
                        <div class="b-registration__text b-registration__text--info-delivery">
                            <p>Параметры подписки: по субботам, раз в неделю, с 10 до 20.</p>
                            <p>Первая доставка: суббота 20.07.2017 с 10 до 20</p>
                        </div>
                    </li>
                    <li class="b-registration__item-delivery"><span class="b-icon b-icon--delivery-calendar"><svg class="b-icon__svg"
                                                                                                                  viewBox="0 0 18 12 "
                                                                                                                  width="18px"
                                                                                                                  height="12px"><use
                                        class="b-icon__use"
                                        xlink:href="icons.svg#icon-delivery-car"></use></svg></span>
                        <div class="b-registration__text b-registration__text--info-delivery">
                            <p>Доставка курьером, по адресу:</p>
                            <p>г. Москва, ул. Ленина, д. 4, кв. 24, под. 3, эт. 4</p>
                        </div>
                    </li>
                    <li class="b-registration__item-delivery"><span class="b-icon b-icon--delivery-calendar"><svg class="b-icon__svg"
                                                                                                                  viewBox="0 0 18 14 "
                                                                                                                  width="18px"
                                                                                                                  height="14px"><use
                                        class="b-icon__use"
                                        xlink:href="icons.svg#icon-delivery-dollar"></use></svg></span>
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
        $APPLICATION->IncludeComponent(
            'fourpaws:personal.referral',
            'popup',
            [],
            $component,
            ['HIDE_ICONS' => 'Y']
        );
    }
    if ($template->hasPersonalAddress()) {
        $APPLICATION->IncludeComponent(
            'fourpaws:personal.address',
            'popup',
            [],
            $component,
            ['HIDE_ICONS' => 'Y']
        );
    }
    if ($template->hasPersonalPet()) {
        $APPLICATION->IncludeComponent(
            'fourpaws:personal.pets',
            'popup',
            [],
            $component,
            ['HIDE_ICONS' => 'Y']
        );
    }
    if ($template->hasPersonalProfile()) {
        $APPLICATION->IncludeComponent(
            'fourpaws:personal.profile',
            'popupChangePassword',
            [],
            $component,
            ['HIDE_ICONS' => 'Y']
        );
        $APPLICATION->IncludeComponent(
            'fourpaws:personal.profile',
            'popupChangeData',
            [],
            $component,
            ['HIDE_ICONS' => 'Y']
        );
        $APPLICATION->IncludeComponent(
            'fourpaws:personal.profile',
            'popupChangePhone',
            [],
            $component,
            ['HIDE_ICONS' => 'Y']
        );
    } ?>
    <div class="b-popup-wrapper__wrapper-modal">
        <section class="b-popup-pick-city b-popup-pick-city--choose-gift js-popup-section"
                 data-popup="popup-choose-gift">
            <a class="b-popup-pick-city__close b-popup-pick-city__close--choose-gift js-close-popup"
               href="javascript:void(0);"
               title="Закрыть"></a>
            <header class="b-popup-pick-city__header-popup b-popup-pick-city__header-popup--choose-gift">
                <h1 class="b-title b-title--h1 b-title--choose-gift">Выберите 2 подарка</h1>
            </header>
            <div class="b-common-item b-common-item--popup-gift">
                <span class="b-common-item__image-wrap b-common-item__image-wrap--popup-gift"><img class="b-common-item__image b-common-item__image--popup-gift"
                                                                                                   src="images/content/hills-cat.jpg"
                                                                                                   alt="для щенков с ягненком и рисом"
                                                                                                   title="" /></span>
                <div
                        class="b-common-item__info-center-block b-common-item__info-center-block--popup-gift">
                    <a class="b-common-item__description-wrap"
                       href="javascript:void(0);"
                       title=""><span class="b-clipped-text b-clipped-text--popup-gift"><span><strong>Хиллс</strong> для щенков с ягненком и рисом</span></span><span
                                class="b-common-item__variant b-common-item__variant--shopping b-common-item__variant--choose-gift"><span
                                    class="b-common-item__name-value">100 г</span></span></a>
                </div>
                <div class="b-choose-radio">
                    <input class="b-choose-radio__input" type="checkbox" name="hills-1" id="id-hills-gift-1" />
                    <label class="b-choose-radio__label" for="id-hills-gift-1"></label>
                </div>
            </div>
            <div class="b-common-item b-common-item--popup-gift">
                <span class="b-common-item__image-wrap b-common-item__image-wrap--popup-gift"><img class="b-common-item__image b-common-item__image--popup-gift"
                                                                                                   src="images/content/clean-cat.jpg"
                                                                                                   alt="собак мелкие и миниатюрные породы Лайт"
                                                                                                   title="" /></span>
                <div
                        class="b-common-item__info-center-block b-common-item__info-center-block--popup-gift">
                    <a class="b-common-item__description-wrap"
                       href="javascript:void(0);"
                       title=""><span class="b-clipped-text b-clipped-text--popup-gift"><span><strong>Хиллс</strong> собак мелкие и миниатюрные породы Лайт</span></span><span
                                class="b-common-item__variant b-common-item__variant--shopping b-common-item__variant--choose-gift"><span
                                    class="b-common-item__name-value">1 кг</span></span></a>
                </div>
                <div class="b-choose-radio">
                    <input class="b-choose-radio__input" type="checkbox" name="hills-2" id="id-hills-gift-2" />
                    <label class="b-choose-radio__label" for="id-hills-gift-2"></label>
                </div>
            </div>
            <div class="b-common-item b-common-item--popup-gift">
                <span class="b-common-item__image-wrap b-common-item__image-wrap--popup-gift"><img class="b-common-item__image b-common-item__image--popup-gift"
                                                                                                   src="images/content/royal-canin-2.jpg"
                                                                                                   alt="для собак мини пород Сеньор"
                                                                                                   title="" /></span>
                <div class="b-common-item__info-center-block b-common-item__info-center-block--popup-gift">
                    <a class="b-common-item__description-wrap"
                       href="javascript:void(0);"
                       title=""><span class="b-clipped-text b-clipped-text--popup-gift"><span><strong>Хиллс</strong> для собак мини пород Сеньор</span></span><span
                                class="b-common-item__variant b-common-item__variant--shopping b-common-item__variant--choose-gift"><span
                                    class="b-common-item__name-value">100 г</span></span></a>
                </div>
                <div class="b-choose-radio">
                    <input class="b-choose-radio__input" type="checkbox" name="hills-3" id="id-hills-gift-3" />
                    <label class="b-choose-radio__label" for="id-hills-gift-3"></label>
                </div>
            </div>
            <div class="b-common-item b-common-item--popup-gift">
                <span class="b-common-item__image-wrap b-common-item__image-wrap--popup-gift"><img class="b-common-item__image b-common-item__image--popup-gift"
                                                                                                   src="images/content/hills-cat.jpg"
                                                                                                   alt="ля собак идеальный вес состоит"
                                                                                                   title="" /></span>
                <div class="b-common-item__info-center-block b-common-item__info-center-block--popup-gift">
                    <a class="b-common-item__description-wrap"
                       href="javascript:void(0);"
                       title=""><span class="b-clipped-text b-clipped-text--popup-gift"><span><strong>Хиллс</strong> ля собак идеальный вес состоит</span></span><span
                                class="b-common-item__variant b-common-item__variant--shopping b-common-item__variant--choose-gift"><span
                                    class="b-common-item__name-value">100 г</span></span></a>
                </div>
                <div class="b-choose-radio">
                    <input class="b-choose-radio__input" type="checkbox" name="hills-4" id="id-hills-gift-4" />
                    <label class="b-choose-radio__label" for="id-hills-gift-4"></label>
                </div>
            </div>
            <div class="b-common-item b-common-item--popup-gift">
                <span class="b-common-item__image-wrap b-common-item__image-wrap--popup-gift"><img class="b-common-item__image b-common-item__image--popup-gift"
                                                                                                   src="images/content/royal-canin-2.jpg"
                                                                                                   alt="для собак миниатюрных пород с ягненком вкусным"
                                                                                                   title="" /></span>
                <div
                        class="b-common-item__info-center-block b-common-item__info-center-block--popup-gift">
                    <a class="b-common-item__description-wrap"
                       href="javascript:void(0);"
                       title=""><span class="b-clipped-text b-clipped-text--popup-gift"><span><strong>Хиллс</strong> для собак миниатюрных пород с ягненком вкусным</span></span><span
                                class="b-common-item__variant b-common-item__variant--shopping b-common-item__variant--choose-gift"><span
                                    class="b-common-item__name-value">100 г</span></span></a>
                </div>
                <div class="b-choose-radio">
                    <input class="b-choose-radio__input" type="checkbox" name="hills-5" id="id-hills-gift-5" />
                    <label class="b-choose-radio__label" for="id-hills-gift-5"></label>
                </div>
            </div>
        </section>
    </div>
    <section class="b-popup-wrapper__wrapper-modal js-popup-section" data-popup="alert-popup" style="display: none;">
        <div class="b-registration b-registration--popup js-popup-alert-title success" data-popup="alert-popup"><a class="b-registration__close js-close-popup" href="javascript:void(0)" title="закрыть"></a>
            <header class="b-registration__header">
                <h1 class="b-title b-title--h1 b-title--popup-success">Успех
                </h1>
                <h1 class="b-title b-title--h1 b-title--popup-error">Ошибка
                </h1>
            </header>
            <div class="b-registration__content b-registration__content--simple-text">
                <div class="b-registration__text-instruction js-popup-simple-text">Код выслан повторно</div>
            </div>
        </div>
    </section>
</div>
