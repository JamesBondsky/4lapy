<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Helper;


class PriceHelper
{
    /**
     * @param float $price
     * @return float
     */
    public static function roundPrice(float $price): float
    {
        return floor($price * 10) / 10;
    }
}