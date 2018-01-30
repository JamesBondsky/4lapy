<?php

namespace FourPaws\DeliveryBundle\Helpers;

use Bitrix\Main\Grid\Declension;
use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\Helpers\CurrencyHelper;

class DeliveryTimeHelper
{
    /**
     * @param CalculationResult $calculationResult
     * @param \DateTime|null $exactDate
     * @param bool $short
     * @param bool $withPrice
     *
     * @return mixed|string
     */
    public static function showTime(
        CalculationResult $calculationResult,
        \DateTime $exactDate = null,
        $short = false,
        $withPrice = true
    ) {
        $result = '';
        switch ($calculationResult->getPeriodType()) {
            case CalculationResult::PERIOD_TYPE_DAY:
                if (!$exactDate) {
                    $date = new \DateTime();
                    $date->modify('+' . ($calculationResult->getPeriodFrom()) . ' days');
                } else {
                    $date = $exactDate;
                }
                if ($short) {
                    $dateFormat = 'D, d M';
                } else {
                    $dateFormat = 'l, d F';
                }
                if ($exactDate) {
                    $dateFormat .= ' в H:00';
                }

                $result = FormatDate($dateFormat, $date->getTimestamp());

                break;
            case CalculationResult::PERIOD_TYPE_HOUR:
                $result .= 'через ';
                $result .= ($calculationResult->getPeriodFrom() == 1) ? '' : ($calculationResult->getPeriodFrom(
                    ) . ' ');
                $result .= (new Declension('час', 'часа', 'часов'))->get($calculationResult->getPeriodFrom());
                break;
        }

        if ($withPrice) {
            if ($short && !$calculationResult->getPrice()) {
                $result .= ', 0 ₽';
            } else {
                $result .= ', ' . CurrencyHelper::formatPrice($calculationResult->getPrice());
            }
        }

        return mb_strtolower($result);
    }
}
