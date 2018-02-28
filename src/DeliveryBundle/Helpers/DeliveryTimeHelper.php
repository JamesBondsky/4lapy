<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Helpers;

use Bitrix\Main\Grid\Declension;
use FourPaws\DeliveryBundle\Entity\CalculationResult;
use FourPaws\Helpers\CurrencyHelper;

class DeliveryTimeHelper
{
    /**
     * @param CalculationResult $calculationResult
     * @param null|\DateTime $exactDate
     * @param array $options
     *                  - SHOW_TIME - отображать ли время
     *                  - SHOW_PRICE - отображать стоимость доставки
     *                  - SHORT - короткий формат вывода
     *                  - DAY_FORMAT - формат или \Closure, вызывается если тип периода доставки "день"
     *                  - HOUR_FORMAT - формат или \Closure, вызывается если тип периода доставки "час"
     *
     * @return string
     */
    public static function showTime(
        CalculationResult $calculationResult,
        \DateTime $exactDate = null,
        array $options = []
    ) {
        $defaultOptions = [
            'SHOW_TIME'   => false,
            'SHORT'       => false,
            'SHOW_PRICE'  => false,
            'DAY_FORMAT'  => null,
            'HOUR_FORMAT' => null,
        ];

        $options = array_merge($defaultOptions, $options);

        $result = '';
        if ($exactDate) {
            $delivery = static::updateDeliveryDate($calculationResult, $exactDate);
        } else {
            $delivery = $calculationResult;
        }

        $date = new \DateTime();
        switch ($delivery->getPeriodType()) {
            case CalculationResult::PERIOD_TYPE_DAY:
                if (!$exactDate) {
                    $date->modify('+' . ($delivery->getPeriodFrom()) . ' days');
                } else {
                    $date = $exactDate;
                }

                if ($options['DAY_FORMAT']) {
                    $options['DAY_FORMAT'] = ($options['DAY_FORMAT'] instanceof \Closure)
                        ? $options['DAY_FORMAT']($date)
                        : $options['DAY_FORMAT'];

                    $result = FormatDate($options['DAY_FORMAT'], $date->getTimestamp());
                } else {
                    if ($options['SHORT']) {
                        $dateFormat = 'D, j M';
                    } else {
                        $dateFormat = 'l, j F';
                    }
                    if ($options['SHOW_TIME']) {
                        $dateFormat .= ' в H:00';
                    }

                    $result = FormatDate($dateFormat, $date->getTimestamp());
                }
                break;
            case CalculationResult::PERIOD_TYPE_HOUR:
                if ($options['HOUR_FORMAT']) {
                    $date->modify('+' . ($delivery->getPeriodFrom()) . ' hours');
                    if ($options['HOUR_FORMAT'] instanceof \Closure) {
                        $options['HOUR_FORMAT'] = $options['HOUR_FORMAT']($date);
                    }

                    $result = FormatDate($options['HOUR_FORMAT'], $date->getTimestamp());
                } else {
                    $result .= 'через ';
                    $result .= ($delivery->getPeriodFrom() == 1) ? '' : ($delivery->getPeriodFrom() . ' ');
                    $result .= (new Declension('час', 'часа', 'часов'))->get($delivery->getPeriodFrom());
                }
                break;
        }

        if ($options['SHOW_PRICE']) {
            if ($options['SHORT'] && !$delivery->getPrice()) {
                $result .= ', 0 ₽';
            } else {
                $result .= ', ' . CurrencyHelper::formatPrice($delivery->getPrice());
            }
        }

        return mb_strtolower($result);
    }

    /**
     * @param CalculationResult $delivery
     * @param \DateTime $deliveryDate
     */
    public static function updateDeliveryDate(CalculationResult $delivery, \DateTime $deliveryDate)
    {
        $deliveryInstance = clone $delivery;
        $date = new \DateTime();
        if ($deliveryDate->format('z') !== $date->format('z')) {
            $deliveryInstance->setPeriodType(CalculationResult::PERIOD_TYPE_DAY);
            $deliveryInstance->setPeriodFrom($deliveryDate->format('z') - $date->format('z'));
        } else {
            $deliveryInstance->setPeriodType(CalculationResult::PERIOD_TYPE_HOUR);
            $deliveryInstance->setPeriodFrom($deliveryDate->format('G') - $date->format('G'));
        }

        return $deliveryInstance;
    }
}
