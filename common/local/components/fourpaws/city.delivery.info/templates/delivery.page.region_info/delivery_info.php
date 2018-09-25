<?php

use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arParams
 * @var array                     $arResult
 * @var array                     $templateData
 *
 * @var string                    $componentPath
 * @var string                    $templateName
 * @var string                    $templateFile
 * @var string                    $templateFolder
 * @var DeliveryResultInterface   $deliveryResult
 * @var bool                      $showHeader
 *
 * @global CUser                  $USER
 * @global CMain                  $APPLICATION
 * @global CDatabase              $DB
 */
?>
<p>
    <strong class="b-delivery__region-item-title">
        <?= $deliveryResult->getDeliveryZone() === DeliveryService::ZONE_1 ? 'Москва' : 'Московская область' ?>
    </strong>
</p>
<p><?= $showHeader ? 'Доставка - ' : '' ?><?= $deliveryResult->getDeliveryPrice() ?> р</p>
<?php if ($deliveryResult->getFreeFrom()) { ?>
    <p><strong>БЕСПЛАТНО</strong> при заказе от <?= $deliveryResult->getFreeFrom() ?>р.</p>
<?php } ?>
<?php if (empty($deliveryResult->getWeekDays()) || \count($deliveryResult->getWeekDays()) === 7) { ?>
    <p>Ежедневно</p>
<?php } else {
    $days = [];
    $currentDate = new \DateTime();
    foreach ($deliveryResult->getWeekDays() as $day) {
        $days[] = FormatDate(
            'l',
            \strtotime(
                \sprintf(
                    'next %s',
                    jddayofweek($day - 1, CAL_DOW_SHORT)
                )
            )
        );
    }
    ?>
    <?= implode(', ', $days); ?>
<?php } ?>
<div class="b-delivery__region-item-time">
    <?php foreach ($deliveryResult->getIntervals() as $interval) { ?>
        <span><?= (string)$interval ?></span>
    <?php } ?>
</div>
