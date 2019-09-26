<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<? $APPLICATION->AddViewContent('is_active_popup', 'active'); ?>

<section class="b-popup-wrapper__wrapper-modal js-popup-section" data-popup="alert-popup" style="display: block;">
    <div class="b-registration b-registration--popup js-popup-alert-title success" data-popup="delivery-warning">
        <a class="b-registration__close js-close-popup" href="javascript:void(0)" title="закрыть"></a>
        <div class="b-registration__content b-registration__content--simple-text">
            <div class="b-registration__text-instruction js-popup-simple-text">
                <div class="delivery-warning">
                    <span style="background-image: url(/static/build/assets/../images/inhtml/info-gray.svg);"></span>
                    Обратите внимание, что доставка в ваш регион не осуществляется.
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .delivery-warning {
        display: block;
        width: 100%;
        text-align: center;
    }

    .delivery-warning span {
        display: block;
        width: 60px;
        height: 60px;
        margin: 0 auto 20px;
        background-repeat: no-repeat;
        background-size: 60px;
    }
</style>
