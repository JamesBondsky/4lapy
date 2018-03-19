<?php
/**
 * Created by PhpStorm.
 * Date: 15.03.2018
 * Time: 14:31
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils;

/**
 * Trait SortByKeyTrait
 * @todo вынести в хелпер
 * @package FourPaws\SaleBundle\Discount\Utils
 */
trait SortByKeyTrait
{
    /**
     * Сортирует массив $array по одному или нескольким ключам $keys
     *
     * @param array $array
     * @param array|string $keys
     *
     * @return bool
     */
    private static function sortByKey(array &$array, $keys): bool
    {
        if (empty($keys) or (empty($array) and \is_array($array))) {
            return true;
        }
        if (!\is_array($array)) {
            return false;
        }
        $keys = (array)$keys;
        $cmp = function ($a, $b) use ($keys) {
            $res = 0;
            /** @var array $keys */
            foreach ($keys as $key => $value) {
                if(\is_string($key)) {
                    $k = $key;
                    $asc = $value !== 'desc';
                } else {
                    $k = $value;
                    $asc = true;
                }

                if ($res = strnatcasecmp(
                    (string)(\is_array($a[$k]) ? \count($a[$k]) : $a[$k]),
                    (string)(\is_array($b[$k]) ? \count($b[$k]) : $b[$k])
                )
                ) {
                    return ($res * ($asc ? 1 : -1));
                }
            }
            return $res;
        };
        return uasort($array, $cmp);
    }
}