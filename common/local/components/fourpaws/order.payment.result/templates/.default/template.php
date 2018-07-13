<?php

use FourPaws\Helpers\PhoneHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<div class="b-container">
    <h1 class="b-title b-title--h1 b-title--order">Ошибка при оплате заказа
    </h1>
    <div class="b-order b-order--top-line">
        <div class="b-order__text-block">
            Попробуйте оплатить заказ еще раз сейчас или в личном кабинете. Позвоните на горячую линию по номеру
            <?= PhoneHelper::getCityPhone() ?>, сообщите о проблеме, и наши операторы помогут вам
        </div>
    </div>
</div>
