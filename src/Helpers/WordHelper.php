<?php

namespace FourPaws\Helpers;

class WordHelper
{
    /**
     * Возвращает нужную форму существительного, стоящего после числительного
     *
     * @param int $number числительное
     * @param array $forms формы слова для 1, 2, 5. Напр. ['дверь', 'двери', 'дверей']
     *
     * @return mixed
     */
    public static function declension(int $number, array $forms)
    {
        $ar = [2, 0, 1, 1, 1, 2];
        $key = ($number % 100 > 4 && $number % 100 < 20) ? 2 : $ar[min($number % 10, 5)];

        return $forms[$key];
    }

    /**
     * @param float $weight
     * @param bool $short
     *
     * @return string
     */
    public static function showWeight(float $weight, $short = false): string
    {
        if ($short) {
            return static::numberFormat($weight / 1000) . ' кг';
        }

        $parts = [];

        $kg = floor($weight / 1000);
        if ($kg) {
            $parts[] = static::numberFormat($kg, 0) . ' кг';
        }

        $g = $weight % 1000;
        if ($g) {
            $parts[] = $g . ' г';
        }

        return implode(' ', $parts);
    }

    /**
         * @param     $number
         * @param int $decimals
         *
         * @return string
         */
        public static function numberFormat($number, int $decimals = 2): string
        {
            return number_format($number, $decimals, '.', ' ');
        }
    }
