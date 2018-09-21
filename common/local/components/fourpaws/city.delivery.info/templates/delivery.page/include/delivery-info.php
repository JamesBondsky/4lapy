<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Dto\IntervalRuleResult;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\WordHelper;

/**
 * @var array $delivery
 */

$isInnerDelivery = $delivery['CODE'] === DeliveryService::INNER_DELIVERY_CODE;
?>
<div class="b-delivery__delivery-type-row">
    <div class="b-delivery__delivery-type-row__title">
        <p>Курьерская доставка</p>
    </div>
    <div class="b-delivery__delivery-type-row__price">
        <p>Стоимость</p>
        <span><?= WordHelper::numberFormat($delivery['PRICE'], 0) ?> ₽</span>
        <?php if (!empty($delivery['FREE_FROM'])) { ?>
            <span>Бесплатно при заказе от <?= WordHelper::numberFormat($delivery['FREE_FROM'], 0) ?> ₽</span>
        <?php } ?>
    </div>
    <div class="b-delivery__delivery-type-row__day">
        <p>Получение</p>
        <?php
        if ($isInnerDelivery) {
            /** @var ArrayCollection $intervalDays */
            $intervalDays = $delivery['INTERVAL_DAYS'];
            $results = [];
            /** @var IntervalRuleResult $intervalResult */
            foreach ($intervalDays as $intervalResult) {
                if ($intervalResult->getDays() === 0) {
                    $results[] = \sprintf(
                        'в день оформления заказа (при оформлении до %s:00)',
                        $intervalResult->getTimeTo()
                    );
                } elseif ($intervalResult->getDays() === 1) {
                    $results[] = \sprintf(
                        'на следующий день (при оформлении %s %s:00)',
                        $intervalResult->getTimeTo() === 0 ? 'после' : 'до',
                        $intervalResult->getTimeTo() === 0 ? $intervalResult->getTimeFrom() : $intervalResult->getTimeTo()
                    );
                } else {
                    $results[] = \sprintf(
                        'в течение %s %s (при оформлении %s %s:00)',
                        $intervalResult->getDays(),
                        WordHelper::declension($intervalResult->getDays(), [
                            'день',
                            'дней',
                            'дней',
                        ]),
                        $intervalResult->getTimeTo() === 0 ? 'после' : 'до',
                        $intervalResult->getTimeTo() === 0 ? $intervalResult->getTimeFrom() : $intervalResult->getTimeTo()
                    );
                }
            } ?>
            <span><?= WordHelper::ucfirst(\implode(', ', $results)) ?></span>
        <?php } else { ?>
            <span>
                Через <?= $delivery['PERIOD_FROM'] ?> <?= WordHelper::declension(
                    $delivery['PERIOD_FROM'],
                    [
                        'день',
                        'дня',
                        'дней',
                    ]
                ) ?>
            </span>
            <?php
        }
        ?>
    </div>
    <div class="b-delivery__delivery-type-row__time">
        <?php
        /** @var IntervalCollection $intervals */
        $intervals = $delivery['INTERVALS'];


        if (!$intervals->isEmpty()) { ?>
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
