<?php

namespace FourPaws\Helpers;

/**
 * Class ArithmeticHelper
 *
 * @package FourPaws\Helpers
 */
class ArithmeticHelper
{
    /**
     * @param float $part
     * @param float $whole
     *
     * @return float
     */
    public static function getPercent(float $part, float $whole) : float
    {
        return ($whole - $part) * 100 / $whole;
    }
}
