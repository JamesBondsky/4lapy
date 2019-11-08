<?php
use FourPaws\App\Application as App;
use FourPaws\LocationBundle\LocationService;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use FourPaws\UserBundle\Service\UserService;
use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

try {
    /** @var UserService $userService */
    $userService = App::getInstance()->getContainer()->get(UserCitySelectInterface::class);
    $selectedCity = $userService->getSelectedCity();

    /** @var LocationService $locationService */
    $locationService = App::getInstance()->getContainer()->get('location.service');
    if ($selectedCity['CODE'] === LocationService::LOCATION_CODE_MOSCOW) { ?>

        <script>
            window.dadataConstraintsLocations = <?= $locationService->getDadataJsonFromLocationArray($selectedCity) ?>;
        </script>
        <section class="b-popup-wrapper__wrapper-modal b-popup-wrapper__wrapper-modal--dostavista-address js-popup-section" data-popup="dostavista-address">
            <div class="b-dostavista-address js-popup-alert-title success">
                <div class="b-dostavista-address__top">
                    <div class="b-dostavista-address__title">
                        Подберем вам наилучший способ доставки
                    </div>
                    <div class="b-dostavista-address__img"></div>
                    <a class="b-dostavista-address__close js-close-popup" href="javascript:void(0)" title="закрыть"></a>
                </div>
                <div class="b-dostavista-address__content">
                    <form class="b-form-dostavista-address js-form-new-address js-hidden-valid-fields active js-dostavista-address-form">
                        <input type="hidden" class="js-city" data-city="Москва">
                        <input type="hidden" class="js-region" data-city="Москва">
                        <div class="b-form-dostavista-address__title">На какой адрес планируете оформить доставку?</div>
                        <div class="b-input-line b-input-line--street js-order-address-street">
                            <div class="b-input-line__label-wrapper">
                                <label class="b-input-line__label" for="order-address-street">
                                    Улица
                                </label>
                                <span class="b-input-line__require">(обязательно)</span>
                            </div>
                            <div class="b-input b-input--registration-form">
                                <input class="b-input__input-field b-input__input-field--registration-form ok suggestions-input" type="text" id="order-address-street" placeholder="" name="street" data-errormsg="Не меньше 3 символов без пробелов" value="">
                                <div class="b-error">
                                    <span class="js-message"></span>
                                </div>
                            </div>
                        </div>
                        <div class="b-radio-tab__address-house">
                            <div class="b-input-line b-input-line--house b-input-line--house-address js-small-input js-only-number js-order-address-house">
                                <div class="b-input-line__label-wrapper">
                                    <label class="b-input-line__label" for="order-address-house">
                                        Дом
                                    </label>
                                    <span class="b-input-line__require">(обяз.)</span>
                                </div>
                                <div class="b-input b-input--registration-form">
                                    <input class="b-input__input-field b-input__input-field--registration-form" type="text" id="order-address-house" placeholder="" name="house" value="">
                                    <div class="b-error">
                                        <span class="js-message"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="b-input-line b-input-line--house">
                                <div class="b-input-line__label-wrapper">
                                    <label class="b-input-line__label" for="order-address-part">
                                        Корпус
                                    </label>
                                </div>
                                <div class="b-input b-input--registration-form">
                                    <input class="b-input__input-field b-input__input-field--registration-form js-regular-field js-only-number js-housing js-no-valid" id="order-address-part" name="building" type="text" value="">
                                    <div class="b-error">
                                        <span class="js-message"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="b-input-line b-input-line--house">
                                <div class="b-input-line__label-wrapper">
                                    <label class="b-input-line__label" for="order-address-entrance">
                                        Подъезд
                                    </label>
                                </div>
                                <div class="b-input b-input--registration-form">
                                    <input class="b-input__input-field b-input__input-field--registration-form js-regular-field js-only-number js-entrance js-no-valid" id="order-address-entrance" name="porch" value="">
                                    <div class="b-error">
                                        <span class="js-message"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="b-input-line b-input-line--house">
                                <div class="b-input-line__label-wrapper">
                                    <label class="b-input-line__label" for="order-address-floor">
                                        Этаж
                                    </label>
                                </div>
                                <div class="b-input b-input--registration-form">
                                    <input class="b-input__input-field b-input__input-field--registration-form js-regular-field js-only-number js-floor js-no-valid" id="order-address-floor" name="floor" type="text" value="">
                                    <div class="b-error">
                                        <span class="js-message"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="b-input-line b-input-line--house">
                                <div class="b-input-line__label-wrapper">
                                    <label class="b-input-line__label" for="order-address-apart">
                                        Кв., офис
                                    </label>
                                </div>
                                <div class="b-input b-input--registration-form">
                                    <input class="b-input__input-field b-input__input-field--registration-form js-regular-field js-only-number js-office js-no-valid" id="order-address-apart" name="apartment" type="text" value="">
                                    <div class="b-error">
                                        <span class="js-message"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="b-dostavista-address__bottom">
                        <div class="b-dostavista-address__address">
                            <div class="item item_dostavista">
                                <span class="b-icon b-icon_dostavista"><?= new SvgDecorator('icon-time', 24, 24) ?></span>
                                <span class="item__text js-delivery-time-dostavista-result">Срок доставки</span>
                            </div>
                            <div class="item js-dostavista-date-result">Будет доступен после ввода адреса</div>
                        </div>
                        <button class="b-button b-button--social b-button--next b-button--next-disable js-dostavista-success-button">
                            Запомнить адрес
                        </button>
                    </div>
                    <div class="b-dostavista-address__primary">Вы&nbsp;можете изменить дату и&nbsp;время доставки при оформлении заказа в&nbsp;корзине</div>
                    <div class="b-radio-tab-map__map" id="map_courier_delivery" style="display: none;">
                    </div>
                </div>
            </div>
        </section>
    <?php }
} catch (\Exception $e) {
} ?>
