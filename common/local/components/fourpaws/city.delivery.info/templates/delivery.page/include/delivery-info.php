<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\Helpers\WordHelper;

?>
<div class="b-delivery__delivery-type-row">
    <div class="b-delivery__delivery-type-row__title">
        <p>Курьерская доставка</p>
    </div>
    <div class="b-delivery__delivery-type-row__price">
        <p>Стоимость</p>
        <span><?= CurrencyHelper::formatPrice($delivery['PRICE']) ?></span>
        <?php if (!empty($delivery['FREE_FROM'])) { ?>
            <span>Бесплатно при заказе от <?= $delivery['FREE_FROM'] ?> ₽</span>
        <? } ?>
    </div>
    <div class="b-delivery__delivery-type-row__day">
        <p>Получение</p>
        <? if ($delivery['CODE'] == DeliveryService::INNER_DELIVERY_CODE) { ?>
            <span>В день оформления заказа (при оформлении до 14:00) или на следующий день</span>
        <? } else { ?>
            <span>
                Через <?= $delivery['PERIOD_FROM'] ?> <?= WordHelper::declension(
                    $delivery['PERIOD_FROM'],
                    ['день', 'дня', 'дней']
                ) ?>
            </span>
        <? } ?>
    </div>
    <div class="b-delivery__delivery-type-row__time">
        <?php if (!empty($delivery['INTERVALS'])) { ?>
            <p>Время</p>
            <?php $lastKey = end(array_keys($delivery['INTERVALS'])) ?>
            <?php foreach ($delivery['INTERVALS'] as $i => $interval) { ?>
                <span>
                    <?= date('H:00', mktime($interval['FROM'])) . ' - ' . date(
                        'H:00',
                        mktime($interval['TO'])
                    ) . ($i !== $lastKey ? ',' : '') ?>
                </span>
            <?php } ?>
        <?php } ?>
    </div>
</div>
