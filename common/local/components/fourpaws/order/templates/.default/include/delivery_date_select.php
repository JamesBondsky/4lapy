<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\SaleBundle\Enum\OrderStorage;

/**
 * @var DeliveryResultInterface $selectorDelivery
 * @var OrderStorage            $selectorStorage
 * @var string                  $selectorName
 * @var FourPawsOrderComponent  $component
 */

$deliveryService = $component->getDeliveryService();
$dates = $deliveryService->getNextDeliveryDates($selectorDelivery, 10);
?>
<select class="b-select__block b-select__block--recall b-select__block--feedback-page js-select-recovery js-change-date js-pickup-date"
        name="<?= $selectorName ?>">
    <option value="" disabled="disabled" selected="selected">выберите</option>
    <?php foreach ($dates as $i => $date) { ?>
        <option value="<?= $i ?>" <?= ($selectorStorage->getDeliveryDate() === $i) ? 'selected="selected"' : '' ?>>
            <?= FormatDate('l, d.m.Y', $date->getTimestamp()) ?>
        </option>
    <?php } ?>
</select>
