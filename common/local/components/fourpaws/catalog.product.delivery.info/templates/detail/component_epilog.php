<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Helpers\WordHelper;
use Bitrix\Sale\Delivery\CalculationResult;

/**
 * @var array $arResult
 * @var CBitrixComponent $this
 */

$deliveryDays = (int)$arResult['CURRENT']['DELIVERY']['PERIOD_FROM'];
$pickupDays = (int)$arResult['CURRENT']['PICKUP']['PERIOD_FROM'];
$pickupPeriodType = $arResult['CURRENT']['PICKUP']['PERIOD_TYPE'];

switch ($deliveryDays) {
    case 0:
        $deliveryDateString = 'Сегодня';
        break;
    case 1:
        $deliveryDateString = 'Завтра';
        break;
    default:
        $deliveryDateString = 'В течение ' . $deliveryDays . ' ' . WordHelper::declension(
                $deliveryDays,
                ['дня', 'дней', 'дней']
            );
}

switch ($pickupPeriodType) {
    case CalculationResult::PERIOD_TYPE_HOUR:
        $date = new DateTime();
        $date->modify('+1 hour');
        if ($date->format('H') < 21) {
            $pickupDateString = 'Сегодня с ' . $date->format('H:i');
        } else {
            $pickupDateString = 'Завтра с 10:00';
        }
        if ($date->format('z') == date('z')) {
            $pickupDateString = 'Сегодня с ' . $date->format('H:i');
        }
        break;
    case CalculationResult::PERIOD_TYPE_DAY:
        switch ($pickupDays) {
            case 0:
                $pickupDateString = 'Сегодня';
                break;
            case 1:
                $pickupDateString = 'Завтра';
                break;
            default:
                $pickupDateString = 'В течение ' . $pickupDays . ' ' . WordHelper::declension(
                        $pickupDays,
                        ['дня', 'дней', 'дней']
                    );
        }
}

$page = ob_get_clean();
$page = str_replace(
    [
        '#DELIVERY_DATE#',
        '#PICKUP_DATE#',
    ],
    [
        $deliveryDateString,
        $pickupDateString,
    ],
    $page
);
echo $page;
