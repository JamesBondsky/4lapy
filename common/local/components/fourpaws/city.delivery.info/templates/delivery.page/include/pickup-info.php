<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\Helpers\WordHelper;

$isInnerPickup = $pickup['CODE'] === DeliveryService::INNER_PICKUP_CODE;
?>
<div class="b-delivery__delivery-type-row">
    <div class="b-delivery__delivery-type-row__title">
        <?php if ($isInnerPickup) { ?>
            <p>Самовывоз из магазина</p>
        <?php } else { ?>
            <p>Самовывоз из пункта выдачи</p>
        <?php } ?>
    </div>
    <div class="b-delivery__delivery-type-row__price">
        <p>Стоимость</p>
        <span><?= ($pickup['PRICE'] >  0) ? (WordHelper::numberFormat($pickup['PRICE'], 0) . ' ₽') : 'Бесплатно'?></span>
        <?php if ($isInnerPickup) { ?>
            <a href="/shops/?codeNearest=<?=$arParams['LOCATION_CODE']?>&findNearest=Y" data-url="<?= $arResult['SHOP_LIST_URL'] ?>" data-code="<?= $arParams['LOCATION_CODE'] ?>">
                Найти ближайший
            </a>
        <? } ?>
    </div>
    <div class="b-delivery__delivery-type-row__day">
        <p>Получение</p>
        <?php if ($isInnerPickup) { ?>
            <span><?=DeliveryTimeHelper::PACKING_TIME_TEXT_C?> после оформления заказа при наличии товара в магазине</span>
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
        <?php if ($isInnerPickup) { ?>
            <p>Время</p>
            <span>В рабочие часы магазина</span>
        <?php } ?>
    </div>
</div>

<?
if ($arParams['LOCATION_CODE'] === '0000073738') { // Москва
	?>
	<div class="b-delivery__delivery-type-row">
		<div class="b-delivery__delivery-type-row__title"><p>Экспресс-доставка</p></div>
		<div class="b-delivery__delivery-type-row__price">
			<p>Стоимость</p>
			<span>397 ₽</span>
			<span>Бесплатно при заказе от 3997 ₽</span>
		</div>
		<div class="b-delivery__delivery-type-row__day">
			<p>Получение</p>
			<span>В течение 3-х часов с момента оформления заказа (при оформлении до 19:00)</span>
		</div>
		<div class="b-delivery__delivery-type-row__time">
			<p>Время</p>
			<span>10:00-21:00</span>
		</div>
	</div>
	<?
}