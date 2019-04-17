<?php
/**
 * Выбор желаемой даты первой доставки и интервала
 */

use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;

/**
 * @var CalculationResultInterface $currentDelivery
 * @var DeliveryService $deliveryService
*/
$deliveryService = $component->getDeliveryService();

if($deliveryService->isDelivery($currentDelivery)){
    /** @var DeliveryResultInterface $currentDelivery */
    $nextDeliveries = $deliveryService->getNextDeliveries($currentDelivery, 10);
    $isDelivery = true;
} else {
    while(count($nextDeliveries)<11){
        $curDate = new \DateTime(sprintf('+%s days', $daysAdd));
        $tmpPickup = clone $currentDelivery;
        $tmpPickup->setCurrentDate($curDate);
        $nextDeliveries[] = $tmpPickup;
    }
}
?>
<div class="b-delivery-type-time" data-container-delivery-type-time="true">
    <ul class="b-radio-tab">
        <li class="b-radio-tab__tab b-radio-tab__tab--default-dostavista" data-content-type-time-delivery="default">
            <div class="delivery-block__type visible">
                <div class="b-input-line b-input-line--desired-date">
                    <div class="b-input-line__label-wrapper">
                        <span class="b-input-line__label">Желаемая дата первой доставки</span>
                    </div>
                    <div class="b-select b-select--recall b-select--feedback-page">
                        <?php
                        $selectorName = 'deliveryDate';
                        include 'delivery_date_select.php'
                        ?>
                    </div>
                </div>
                <?php if ($isDelivery && !$currentDelivery->getIntervals()->isEmpty()) {
                    $selectorName = 'deliveryInterval';
                    include 'delivery_interval_select.php';
                } ?>
            </div>
        </li>
    </ul>
</div>
