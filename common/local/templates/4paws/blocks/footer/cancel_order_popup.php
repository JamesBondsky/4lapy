<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>

<section class="b-popup-wrapper__wrapper-modal js-popup-section" data-popup="cancel-order" style="display: none;">
    <div class="b-popup-action-order b-popup-action-order--popup" data-popup="cancel-order">
        <div class="b-popup-change-viewport__close js-close-popup"></div>
        <div class="b-popup-action-order b-popup-action-order-container js-info" style="display: flex;">
            <h3 class="b-popup-action-order b-popup-action-order__title">Вы хотите отменить заказ?</h3>
            <div class="b-popup-action-order b-popup-action-order-buttons">
                <div class="b-popup-action-order b-popup-action-order__button b-popup-action-order__button-yes js-cancel-order">Да</div>
                <div class="b-popup-action-order b-popup-action-order__button b-popup-action-order__button-no js-close-popup">Нет</div>
            </div>
        </div>
        <div class="b-popup-action-order b-popup-action-order-container js-result" style="display: none;">
            <h3 class="b-popup-action-order b-popup-action-order__title js-result-text"></h3>
        </div>
    </div>
</section>
