<?php
/** global CUser $USER */
global $USER;
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if ($arResult['ECOMMERCE_VIEW_SCRIPT']) {
    echo $arResult['ECOMMERCE_VIEW_SCRIPT'];
}

if ($arParams['LOAD_TYPE'] !== 'default') { ?>
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
        <?= $arResult['USER_NAME']; ?>, спасибо за заказ!
    </p>
    <p class="b-popup-one-click__description b-popup-one-click__description--complite">
        Номер вашего заказа № <?= $arResult['ACCOUNT_NUMBER']; ?>. Оператор свяжется с вами для уточнения деталей
        доставки. <br>
        <?php
        if ($USER->IsAuthorized()) {
            ?>
            Перейти в <a class="b-link b-link--orange b-link--inherit" href="/personal/"
                         title="Личный кабинет">личный
                кабинет</a>.
            <?php
        } else {
            ?>
            Теперь у вас есть <a class="b-link b-link--inherit b-link--orange js-open-popup"
                                 data-popup-id="authorization"
                                 href="javascript:void(0)"
                                 title="личный кабинет">личный
                кабинет</a>, в котором можно узнать статус своего заказа или добавить адрес доставки.
            <?php
        }
        ?>
    </p>
    <p>
        <a class="b-link b-link--orange b-link--inherit" href="/catalog/" title="Продолжить покупки.">Продолжить
            покупки</a>.
    </p>
    <br><br>
    <?php

    if ($arParams['LOAD_TYPE'] !== 'default') {
        ?>
        <button class="b-button b-button--one-click b-close js-close-popup">Хорошо</button>
    <?php } else { ?>
        <button class="b-button b-button--one-click b-close js-close-popup" onclick="location.href='/'">
            На главную
        </button>
    <?php } ?>
</div>
