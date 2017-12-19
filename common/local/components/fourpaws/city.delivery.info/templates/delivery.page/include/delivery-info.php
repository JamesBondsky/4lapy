<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="b-delivery__delivery-type-row">
    <div class="b-delivery__delivery-type-row__title">
        <p>Курьерская доставка</p>
    </div>
    <div class="b-delivery__delivery-type-row__price">
        <p>Стоимость</p>
        <span><?= $delivery['PRICE'] ?> ₽</span>
        <?php if (!empty($delivery['FREE_FROM'])) { ?>
            <span>Бесплатно при заказе от <?= $delivery['FREE_FROM'] ?> ₽</span>
        <? } ?>
    </div>
    <div class="b-delivery__delivery-type-row__day">
        <p>Получение</p>
        <span>В день оформления заказа (при оформлении до 14:00) или на следующий день</span>
    </div>
    <div class="b-delivery__delivery-type-row__time">
        <p>Время</p><span>09:00 - 18:00,</span> <span>18:00 - 23:00</span>
    </div>
</div>
