<?php
/**
 * Created by PhpStorm.
 * Date: 02.04.2018
 * Time: 17:32
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\Helpers;


/**
 * Class ArrayHelper
 * @package FourPaws\Helpers
 */
class ArrayHelper
{
    /**
     * Функция проверяет одинаковы ли переданные массивы.
     * <b>При проверке учитываются только строковые ключи, числовые сравниваются только по значению.
     * Рекурсии есть.</b>
     *
     * @param array $one
     * @param array $two
     *
     * @return bool
     */
    public static function arraysEquals(array $one, array $two): bool
    {
        self::recursiveSort($one);
        self::recursiveSort($two);
        /** @noinspection TypeUnsafeComparisonInspection */
        return $one == $two;
    }

    /**
     * Сортирует массив рекурсивно с сохранением <b>ТОЛЬКО</b> строковых ключей
     *
     * @param array $array
     *
     * @return bool
     */
    public static function recursiveSort(array &$array): bool
    {
        foreach ($array as &$value) {
            if (\is_array($value)) {
                self::recursiveSort($value);
            }
        }
        return array_multisort($array, SORT_NATURAL);
    }
}