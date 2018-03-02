<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<?php if ($arParams['LOAD_TYPE'] !== 'default') { ?>
    <div class="b-popup-one-click__close-bar">
        <a class="b-popup-one-click__close js-close-popup" href="javascript:void(0)" title="Закрыть"></a>
        <h1 class="b-title b-title--one-click b-title--one-click-head">Быстрый заказ</h1>
    </div>
<?php } else { ?>
    <div class="b-popup-one-click__close-bar">
        <h1 class="b-title b-title--one-click b-title--one-click-head">Быстрый заказ</h1>
    </div>
<?php } ?>
<div class="b-popup-one-click__form">
    <p class="b-popup-one-click__description b-popup-one-click__description--complite">
        Спасибо за заказ! В ближайшее время с вами свяжется наш менеджер для подтверждения заказа.
    </p>
    <div class="b-text-block b-text-block--one-click">
        <h5 class="b-text-block__list-heading">Так же мы создали вам личный кабинет где вы можете:
        </h5>
        <ul class="b-text-block__list">
            <li>отслеживать статус заказа;</li>
            <li>повторять заказы в 1 клик;</li>
            <li>управлять адресами доставки.</li>
        </ul>
        <p>
            Перейти в <a class="b-link b-link--orange b-link--inherit" href="/personal/" title="Личный кабинет">личный
                кабинет</a>.
        </p>
    </div>
    <?php if ($arParams['LOAD_TYPE'] !== 'default') { ?>
        <button class="b-button b-button--one-click b-close js-close-popup">Хорошо</button>
    <?php } else { ?>
        <button class="b-button b-button--one-click b-close js-close-popup" onclick="location.href='/'">
            На главную
        </button>
    <?php } ?>
</div>
