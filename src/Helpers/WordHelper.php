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

    public static function showWeight(float $weight, $short = false)
    {
        if ($short) {
            return $weight / 1000 . ' кг';
        }

        $parts = [];

        $kg = floor($weight / 1000);
        if ($kg) {
            $parts[] = $kg . ' кг';
        }

        $g = $weight % 1000;
        if ($g) {
            $parts[] = $g . ' г';
        }

        return implode(' ', $parts);
    }

    /**
     * Враппер для FormatDate. Доп. возможности
     *  - ll - отображение для недели в винительном падеже (в пятницу, в субботу)
     *
     * @param string $dateFormat
     * @param int $timestamp
     *
     * @return string
     */
    public static function formatDate(string $dateFormat, int $timestamp)
    {
        if (false !== mb_strpos($dateFormat, 'll')) {
            $date = (new \DateTime)->setTimestamp($timestamp);
            $str = null;
            switch ($date->format('w')) {
                case 0:
                    $str = 'в воскресенье';
                    break;
                case 1:
                    $str = 'в понедельник';
                    break;
                case 2:
                    $str = 'во вторник';
                    break;
                case 3:
                    $str = 'в среду';
                    break;
                case 4:
                    $str = 'в четверг';
                    break;
                case 5:
                    $str = 'в пятницу';
                    break;
                case 6:
                    $str = 'в субботу';
                    break;
            }
            if (null !== $str) {
                $dateFormat = str_replace('ll', $str, $dateFormat);
            }
        }

        return FormatDate($dateFormat, $timestamp);
    }
}
