<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\DeliveryBundle\Service\DeliveryService;

?>
<div class="b-delivery__delivery-type-row">
    <div class="b-delivery__delivery-type-row__title">
        <?php if ($pickup['CODE'] == DeliveryService::INNER_PICKUP_CODE) { ?>
            <p>Самовывоз из магазина</p>
        <?php } else { ?>
            <p>Самовывоз из пункта выдачи</p>
        <?php } ?>
    </div>
    <div class="b-delivery__delivery-type-row__price">
        <p>Стоимость</p>
        <p><?= $pickup['PRICE'] == 0 ? 'Бесплатно' : $pickup['PRICE'] . '₽' ?></p>
        <?php if ($pickup['CODE'] == DeliveryService::INNER_PICKUP_CODE) { ?>
            <a href="#">Найти ближайший</a>
        <? } ?>
    </div>
    <div class="b-delivery__delivery-type-row__day">
        <p>Получение</p>
        <?php if ($pickup['CODE'] == DeliveryService::INNER_PICKUP_CODE) { ?>
            <span>Через 1 час после оформления заказа при наличии товара в магазине</span>
        <?php } else { ?>
            <span>
                <? /** @todo вывод даты самовывоза DPD */ ?>
            </span>
        <?php } ?>
    </div>
    <div class="b-delivery__delivery-type-row__time">
        <p>Время</p>
        <?php if ($pickup['CODE'] == DeliveryService::INNER_PICKUP_CODE) { ?>
            <span>В рабочие часы магазина</span>
        <?php } else { ?>
            <span>
                <? /** @todo вывод времени работы пункта самовывоза DPD */ ?>
            </span>
        <?php } ?>
    </div>
</div>
