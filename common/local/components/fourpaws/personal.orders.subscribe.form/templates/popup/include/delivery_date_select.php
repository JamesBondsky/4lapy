<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use Bitrix\Main\Type\DateTime;

?>

<select class="b-select__block b-select__block--recall b-select__block--feedback-page js-select-recovery js-change-date js-pickup-date <?=$deliveryService->isPickup($currentDelivery) || $hideFirstDateSelect ? 'js-no-valid' : ''?>" <?=$deliveryService->isPickup($currentDelivery) ? 'disabled' : ''?> name="<?= $selectorName ?>">
    <? if($selectedFirstDate instanceof DateTime) { ?>
        <option value="<?=$selectedFirstDate->format('d.m.Y')?>" selected="selected" data-date-option="<?= FormatDate('l, Y-m-d', $selectedFirstDate->getTimestamp()) ?>"><?=$selectedFirstDate->format('d.m.Y')?></option>
    <? } else { ?>
        <option value="" disabled="disabled" selected="selected">выберите</option>
        <?php
        /** @var CalculationResultInterface $nextDelivery */
        foreach ($nextDeliveries as $i => $nextDelivery) { ?>
            <option value="<?= $nextDelivery->getDeliveryDate()->format('d.m.Y') ?>" data-date-option="<?= FormatDate('l, Y-m-d', $nextDelivery->getDeliveryDate()->getTimestamp()) ?>">
                <?= FormatDate('l, d.m.Y', $nextDelivery->getDeliveryDate()->getTimestamp()) ?>
            </option>
        <?php } ?>
    <? } ?>
</select>
