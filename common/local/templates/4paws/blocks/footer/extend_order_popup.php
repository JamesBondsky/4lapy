<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>

<section class="b-popup-wrapper__wrapper-modal js-popup-section" data-popup="extend-order" style="display: none;">
    <div class="b-popup-action-order b-popup-action-order--popup" data-popup="extend-order">
        <div class="b-popup-change-viewport__close js-close-popup"></div>
        <div class="b-popup-action-order b-popup-action-order-container js-info" style="display: flex;">
            <h3 class="b-popup-action-order b-popup-action-order__title">Вы хотите продлить срок хранения до 5-ти дней?</h3>
            <div class="b-popup-action-order b-popup-action-order-buttons">
                <div class="b-popup-action-order b-popup-action-order__button b-popup-action-order__button-yes js-extend-order">Да</div>
                <div class="b-popup-action-order b-popup-action-order__button b-popup-action-order__button-no js-close-popup">Нет</div>
            </div>
        </div>
        <div class="b-popup-action-order b-popup-action-order-container js-result" style="display: none;">
            <h3 class="b-popup-action-order b-popup-action-order__title js-result-text"></h3>
        </div>
    </div>
</section>
