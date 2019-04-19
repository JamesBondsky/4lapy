<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;



?>

<select class="b-select__block b-select__block--recall b-select__block--feedback-page js-select-recovery js-change-date js-pickup-date <?=$deliveryService->isPickup($currentDelivery) ? 'js-no-valid' : ''?>"
        <?=$deliveryService->isPickup($currentDelivery) ? 'disabled' : ''?>
        name="<?= $selectorName ?>">
    <option value="" disabled="disabled" selected="selected">выберите</option>
    <?php
    /** @var CalculationResultInterface $nextDelivery */
    foreach ($nextDeliveries as $i => $nextDelivery) { ?>
        <option value="<?= $nextDelivery->getDeliveryDate()->format('d.m.Y') ?>" data-date-option="<?= FormatDate('l, Y-m-d', $nextDelivery->getDeliveryDate()->getTimestamp()) ?>" <?=($selectedFirstDate == $nextDelivery->getDeliveryDate()->format('d.m.Y') ? 'selected' : '')?>>
            <?= FormatDate('l, d.m.Y', $nextDelivery->getDeliveryDate()->getTimestamp()) ?>
        </option>
    <?php } ?>
</select>
