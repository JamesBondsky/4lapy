<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>

<script>
    window.dadataConstraintsLocations = {"city": "\u041c\u043e\u0441\u043a\u0432\u0430", "kladr_id": "77000000000"};
</script>
<section class="b-popup-wrapper__wrapper-modal js-popup-section opened" data-popup="dostavista-address" style="display: block;">
    <div class="b-registration b-registration--popup js-popup-alert-title success" data-popup="alert-popup">
        <a class="b-registration__close js-close-popup" href="javascript:void(0)" title="закрыть"></a>
        <form class="b-radio-tab__new-address js-form-new-address js-hidden-valid-fields active js-dostavista-address-form" style="display:block">
            <input type="hidden" class="js-city" data-city="Москва">
            <input type="hidden" class="js-region" data-city="Москва">
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
                        <span class="b-input-line__require">(обязательно)</span>
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
        <div style="display: flex; justify-content: space-between;">
            <div style="font-size: 13px; margin: 0 auto;">
                <span class="js-dostavista-date-result">Будет доступен после ввода адреса</span>
            </div>
            <button class="b-button b-button--social b-button--next b-button--fixed-bottom b-button--next-disable js-dostavista-success-button" style="margin: 0 auto;">
                Выбрать адрес
            </button>
        </div>
        <div class="b-radio-tab-map__map" id="map_courier_delivery" style="display: none;">
        </div>
    </div>
</section>
