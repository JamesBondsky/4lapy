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
            <a href="/company/shops/?codeNearest=<?=$arParams['LOCATION_CODE']?>&findNearest=Y" data-url="<?= $arResult['SHOP_LIST_URL'] ?>" data-code="<?= $arParams['LOCATION_CODE'] ?>">
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
                Через <?= $pickup['PERIOD_FROM'] ?> <?= WordHelper::declension(
                    $pickup['PERIOD_FROM'],
                    ['день', 'дня', 'дней']
                ) ?>
            </span>
        <?php } ?>
    </div>
    <div class="b-delivery__delivery-type-row__time">
        <?php if ($pickup['CODE'] == DeliveryService::INNER_PICKUP_CODE) { ?>
            <p>Время</p>
            <span>В рабочие часы магазина</span>
        <?php } ?>
    </div>
</div>
