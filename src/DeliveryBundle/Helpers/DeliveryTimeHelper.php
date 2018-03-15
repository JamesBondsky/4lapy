<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Helpers;

use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\Helpers\DateHelper;

class DeliveryTimeHelper
{
    /**
     * @param CalculationResultInterface $calculationResult
     * @param array array $options
     *                  - SHOW_TIME - отображать ли время
     *                  - SHOW_PRICE - отображать стоимость доставки
     *                  - SHORT - короткий формат вывода
     *                  - DAY_FORMAT - формат или \Closure, вызывается если тип периода доставки "день"
     *                  - HOUR_FORMAT - формат или \Closure, вызывается если тип периода доставки "час"
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public static function showTime(
        CalculationResultInterface $calculationResult,
        array $options = []
    ): string {
        $defaultOptions = [
            'SHOW_TIME'   => false,
            'SHORT'       => false,
            'SHOW_PRICE'  => false,
            'DAY_FORMAT'  => null,
            'HOUR_FORMAT' => null,
        ];

        $options = array_merge($defaultOptions, $options);

        $currentDate = new \DateTime();
        $date = clone $calculationResult->getDeliveryDate();

        if ($options['SHOW_TIME'] && abs($date->getTimestamp() - $currentDate->getTimestamp()) < 2 * 3600) {
            if ($options['HOUR_FORMAT']) {
                if ($options['HOUR_FORMAT'] instanceof \Closure) {
                    $options['HOUR_FORMAT'] = $options['HOUR_FORMAT']($date);
                }

                $result = DateHelper::formatDate($options['HOUR_FORMAT'], $date->getTimestamp());
            } else {
                $result = 'через час';
            }
        } else {
            if ($options['DAY_FORMAT']) {
                $options['DAY_FORMAT'] = ($options['DAY_FORMAT'] instanceof \Closure)
                    ? $options['DAY_FORMAT']($date)
                    : $options['DAY_FORMAT'];

                $result = DateHelper::formatDate($options['DAY_FORMAT'], $date->getTimestamp());
            } else {
                if ($options['SHORT']) {
                    $dateFormat = 'D, j M';
                } else {
                    $dateFormat = 'll, j F';
                }
                if ($options['SHOW_TIME']) {
                    $dateFormat .= ' в H:00';
                }

                $result = DateHelper::formatDate($dateFormat, $date->getTimestamp());
            }
        }

        if ($options['SHOW_PRICE']) {
            if ($options['SHORT'] && !$calculationResult->getPrice()) {
                $result .= ', 0 ₽';
            } else {
                $result .= ', ' . CurrencyHelper::formatPrice($calculationResult->getPrice(), true);
            }
        }

        return mb_strtolower($result);
    }
}
