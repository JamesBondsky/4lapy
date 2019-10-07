<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>

<section class="b-popup-wrapper__wrapper-modal js-popup-section" data-popup="cancel-order" style="display: none;">
    <div class="b-popup-cancel-order b-popup-cancel-order--popup" data-popup="cancel-order">
        <div class="b-popup-cancel-order b-popup-cancel-order-container js-info" style="display: flex;">
            <h3 class="b-popup-cancel-order b-popup-cancel-order__title">Вы хотите отменть заказ?</h3>
            <div class="b-popup-cancel-order b-popup-cancel-order-buttons">
                <div class="b-popup-cancel-order b-popup-cancel-order__button-yes js-cancel-order">Да</div>
                <div class="b-popup-cancel-order b-popup-cancel-order__button-no js-close-popup">Нет</div>
            </div>
        </div>
        <div class="b-popup-cancel-order b-popup-cancel-order-container js-result" style="display: none;">
            <h3 class="b-popup-cancel-order b-popup-cancel-order__title js-result-text"></h3>
        </div>
    </div>
</section>
