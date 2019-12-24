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
    const  UNACTIVE_DATES = [
        '2' => '01.01.2020',
        '1' => '02.01.2020',
    ];
    
    /**
     * Время на сборку товара в магазине
     */
    const PACKING_TIME_TEXT = 'через 30 минут';
    const PACKING_TIME_TEXT_C = 'Через 30 минут';

    /**
     * @param CalculationResultInterface $calculationResult
     * @param array array $options
     *                  - SHOW_TIME - отображать ли время
     *                  - SHOW_PRICE - отображать стоимость доставки
     *                  - SHORT - короткий формат вывода
     *                  - DATE_FORMAT - формат вывода даты
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
        return static::showByDate(
            clone $calculationResult->getDeliveryDate(),
            $calculationResult->getPrice(),
            $options
        );
    }

    /**
     * @param \DateTime $date
     * @param int $price
     * @param array $options
     *                  - SHOW_TIME - отображать ли время
     *                  - SHOW_PRICE - отображать стоимость доставки
     *                  - SHORT - короткий формат вывода
     *                  - DATE_FORMAT - формат вывода даты
     * @return string
     */
    public static function showByDate(\DateTime $date, $price = 0, array $options = []): string
    {
        $nyDate = $date->format('d.m.Y');
        
        if (in_array($nyDate, self::UNACTIVE_DATES)) {
            $date->add(\DateInterval::createfromdatestring('+' . array_search($nyDate, self::UNACTIVE_DATES). ' day'));
        }

        $defaultOptions = [
            'SHOW_TIME' => false,
            'SHORT' => false,
            'SHOW_PRICE' => false,
            'DATE_FORMAT' => null,
        ];
        $currentDate = new \DateTime();

        $options = array_merge($defaultOptions, $options);

        if ($options['SHOW_TIME'] && abs($date->getTimestamp() - $currentDate->getTimestamp()) < 2 * 3600) {
            $result = self::PACKING_TIME_TEXT;
        } else {
            if (!$options['DATE_FORMAT']) {
                if ($options['SHORT']) {
                    $dateFormat = 'D, j M';
                } else {
                    $dateFormat = 'll, j F';
                }
            } else {
                $dateFormat = $options['DATE_FORMAT'];
            }

            if ($options['SHOW_TIME']) {
                $dateFormat .= ' с H:00';
            }

            $result = DateHelper::formatDate($dateFormat, $date->getTimestamp());
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
