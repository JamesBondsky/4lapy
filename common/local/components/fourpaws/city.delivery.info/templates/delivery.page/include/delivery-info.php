<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Entity\Interval;
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
        <?php if (!empty($delivery['FREE_FROM'])) {
    ?>
            <span>Бесплатно при заказе от <?= CurrencyHelper::formatPrice($delivery['FREE_FROM']) ?></span>
        <?php
} ?>
    </div>
    <div class="b-delivery__delivery-type-row__day">
        <p>Получение</p>
        <?php if ($delivery['CODE'] === DeliveryService::INNER_DELIVERY_CODE) {
        ?>
            <span>В день оформления заказа (при оформлении до 14:00) или на следующий день</span>
        <?php
    } else {
        ?>
            <span>
                Через <?= $delivery['PERIOD_FROM'] ?> <?= WordHelper::declension(
                    $delivery['PERIOD_FROM'],
                    ['день', 'дня', 'дней']
                ) ?>
            </span>
        <?php
    } ?>
    </div>
    <div class="b-delivery__delivery-type-row__time">
        <?php
        /** @var IntervalCollection $intervals */
        $intervals = $delivery['INTERVALS'];

        ?>
        <?php if (!$intervals->isEmpty()) {
            ?>
            <p>Время</p>
            <?php
            $intervalData = [];
            /** @var Interval $interval */
            foreach ($intervals as $i => $interval) {
                $intervalData[] = (string)$interval;
            } ?>
            <span><?= implode(', ', $intervalData) ?></span>
        <?php
        } ?>
    </div>
</div>
