<?php

use Bitrix\Main\Grid\Declension;
use FourPaws\DeliveryBundle\Dto\IntervalRuleResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array                             $arResult
 * @var FourPawsCityDeliveryInfoComponent $this
 */


$delivery = $arResult['DELIVERY']['RESULT'];
if (!$delivery instanceof DeliveryResultInterface) {
    return;
}

$currentDate = (new \DateTime())->setTime(0, 0, 0, 0);
/** @noinspection PhpUnhandledExceptionInspection */
$intervalDays = $this->getIntervalDays(
    $delivery,
    $currentDate
);
$intervalHtml = '';
/** @var IntervalRuleResult $intervalResult */
foreach ($intervalDays as $intervalResult) {
    ob_start();
    ?>
    <tr class="b-tab-shipping__tr b-tab-shipping__tr--first-line">
        <td class="b-tab-shipping__td b-tab-shipping__td--first">
            <?php if ($intervalResult->getTimeTo() === 0) { ?>
                после <?= $intervalResult->getTimeFrom() ?>:00
            <?php } else { ?>
                до <?= $intervalResult->getTimeTo() ?>:00
            <?php } ?>
        </td>
        <td class="b-tab-shipping__td b-tab-shipping__td--second">
            <?php if ($intervalResult->getDays() === 0) { ?>
                в тот же день
            <?php } elseif ($intervalResult->getDays() === 1) { ?>
                на следующий день
            <?php } else { ?>
                <?= \FourPaws\Helpers\DateHelper::formatDate(
                    'll',
                    (clone $currentDate)->modify(\sprintf('+% days', $intervalResult->getDays()))->getTimestamp()
                ); ?>
            <?php } ?>
        </td>
    </tr>
    <?php $intervalHtml .= ob_get_clean();
}
?>
<script>
    window.FourPawsCityDeliveryInfoComponentHtml = <?= json_encode($intervalHtml) ?>;
</script>
