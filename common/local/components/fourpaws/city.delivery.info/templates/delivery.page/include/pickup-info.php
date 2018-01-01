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
        <?php if ($pickup['CODE'] == DeliveryService::INNER_PICKUP_CODE) { ?>
            <p>Самовывоз из магазина</p>
        <?php } else { ?>
            <p>Самовывоз из пункта выдачи</p>
        <?php } ?>
    </div>
    <div class="b-delivery__delivery-type-row__price">
        <p>Стоимость</p>
        <span><?= CurrencyHelper::formatPrice($pickup['PRICE']) ?></span>
        <?php if ($pickup['CODE'] == DeliveryService::INNER_PICKUP_CODE) { ?>
            <a href="#" data-url="<?= $arResult['SHOP_LIST_URL'] ?>" data-code="<?= $arParams['LOCATION_CODE'] ?>">
                Найти ближайший
            </a>
        <? } ?>
    </div>
    <div class="b-delivery__delivery-type-row__day">
        <p>Получение</p>
        <?php if ($pickup['CODE'] == DeliveryService::INNER_PICKUP_CODE) { ?>
            <span>Через 1 час после оформления заказа при наличии товара в магазине</span>
        <?php } else { ?>
            <span>
                В течение <?= $pickup['PERIOD_FROM'] ?> <?= WordHelper::declension(
                    $pickup['PERIOD_FROM'],
                    ['день', 'дня', 'дней']
                ) ?>
            </span>
        <?php } ?>
    </div>
    <div class="b-delivery__delivery-type-row__time">
        <p>Время</p>
        <?php if ($pickup['CODE'] == DeliveryService::INNER_PICKUP_CODE) { ?>
            <span>В рабочие часы магазина</span>
        <?php } else { ?>
            <?php if (!empty($delivery['INTERVALS'])) { ?>
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
        <?php } ?>
    </div>
</div>
