<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Helpers;

use Bitrix\Main\ArgumentException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\Helpers\DateHelper;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

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
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @throws StoreNotFoundException
     * @throws NotFoundException
     */
    public static function showTime(
        CalculationResultInterface $calculationResult,
        array $options = []
    ): string {
        $defaultOptions = [
            'SHOW_TIME' => false,
            'SHORT' => false,
            'SHOW_PRICE' => false,
            'DAY_FORMAT' => null,
            'HOUR_FORMAT' => null,
        ];

        $options = array_merge($defaultOptions, $options);

        $date = clone $calculationResult->getDeliveryDate();

        return static::showByDate($date, $calculationResult->getPrice(), $options);
    }

    /**
     * @param \DateTime $date
     * @param int $price
     * @param array $options
     *                  - SHOW_TIME - отображать ли время
     *                  - SHOW_PRICE - отображать стоимость доставки
     *                  - SHORT - короткий формат вывода
     *                  - DAY_FORMAT - формат или \Closure, вызывается если тип периода доставки "день"
     *                  - HOUR_FORMAT - формат или \Closure, вызывается если тип периода доставки "час"
     * @return string
     */
    public static function showByDate(\DateTime $date, $price = 0, array $options = []): string
    {
        $defaultOptions = [
            'SHOW_TIME' => false,
            'SHORT' => false,
            'SHOW_PRICE' => false,
            'DAY_FORMAT' => null,
            'HOUR_FORMAT' => null,
        ];
        $currentDate = new \DateTime();

        $options = array_merge($defaultOptions, $options);
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
                    $dateFormat .= ' с H:00';
                }

                $result = DateHelper::formatDate($dateFormat, $date->getTimestamp());
            }
        }

        if ($options['SHOW_PRICE']) {
            if ($options['SHORT'] && !$price) {
                $result .= ', 0 ₽';
            } else {
                $result .= ', ' . CurrencyHelper::formatPrice($price, true);
            }
        }

        return mb_strtolower($result);
    }
}
