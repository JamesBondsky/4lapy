<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
?>

<select class="b-select__block b-select__block--recall b-select__block--feedback-page js-select-recovery js-change-date js-pickup-date <?=$isHidden ? 'js-no-valid' : ''?>"
        <?=$isHidden ? 'disabled' : ''?>
        name="<?= $selectorName ?>">
    <option value="" disabled="disabled" <?=(!$selectedFirstDate) ? 'selected="selected"' : '' ?>>выберите</option>
    <? /** @var CalculationResultInterface $nextDelivery */
    foreach ($nextDeliveries as $i => $nextDelivery) { ?>
        <option value="<?= $nextDelivery->getDeliveryDate()->format('d.m.Y') ?>"
                data-date-option="<?= FormatDate('l, Y-m-d', $nextDelivery->getDeliveryDate()->getTimestamp()) ?>"
                <?=($selectedFirstDate && $selectedFirstDate->format('d.m.Y') == $nextDelivery->getDeliveryDate()->format('d.m.Y')) ? 'selected="selected"' : '' ?>>
            <?= FormatDate('l, d.m.Y', $nextDelivery->getDeliveryDate()->getTimestamp()) ?>
        </option>
    <? } ?>
</select>
