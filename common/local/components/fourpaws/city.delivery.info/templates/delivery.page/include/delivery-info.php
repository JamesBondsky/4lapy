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
        /** @var int[] $weekDays */
        $weekDays = $delivery['WEEK_DAYS'];
        if ($weekDays && count($weekDays) < 7) {
            ?>
            Дни доставки: <?= implode(', ', $weekDays) ?>.
            <?php
        }

        if ($isInnerDelivery) {
            /** @var ArrayCollection $intervalDays */
            $intervalDays = $delivery['INTERVAL_DAYS'];
            $results = [];
            /** @var IntervalRuleResult $intervalResult */
            foreach ($intervalDays as $intervalResult) {
                $result = '';
                if ($intervalResult->getDays() === 0) {
                    $result .= 'в день оформления заказа';
                } elseif ($intervalResult->getDays() === 1) {
                    $result .= 'на следующий день доставки';
                } else {
                    $result .= \sprintf(
                        'через %s %s',
                        $intervalResult->getDays(),
                        WordHelper::declension($intervalResult->getDays(), [
                            'день',
                            'дня',
                            'дней',
                        ])
                    );
                }

                if (!($intervalResult->getTimeTo() === 0 && $intervalResult->getTimeFrom() === 0)) {
                    $result .= \sprintf(
                        ' (при оформлении %s %s:00)',
                        $intervalResult->getTimeTo() === 0 ? 'после' : 'до',
                        $intervalResult->getTimeTo() === 0 ? $intervalResult->getTimeFrom() : $intervalResult->getTimeTo()
                    );
                }

                $results[] = $result;
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
