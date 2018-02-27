<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<section class="b-popup-wrapper__wrapper-modal js-popup-section" data-popup="buy-one-click">
    <section class="b-popup-one-click js-popup-section" data-popup="buy-one-click">
        <div class="b-popup-one-click__close-bar">
            <a class="b-popup-one-click__close js-close-popup" href="javascript:void(0)" title="Закрыть"></a>
            <h1 class="b-title b-title--one-click b-title--one-click-head">Быстрый заказ</h1>
        </div>
        <form class="b-popup-one-click__form js-form-validation js-phone js-popup-buy-one-click"
              data-url="/ajax/fast-order/create/" method="get">
        </form>
    </section>
</section>
