<?php
/**
 * Created by PhpStorm.
 * Date: 02.07.2018
 * Time: 15:09
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils;


/**
 * Trait discountDisjunction
 * @package FourPaws\SaleBundle\Discount\Utils
 */
trait DiscountDisjunction
{
    /**
     * перенумеровывает группы исходя из того какие номера уже есть. Нужно для ИЛИ между предпосылками.
     *
     * @param array $basket
     * @param array $basketPart
     *
     * @return array
     */
    public static function discountDisjunction(array $basket, array $basketPart): array
    {
        if ($basket) {
            $allExistGroups = [];
            foreach ($basket as $basketItem) {
                /** @noinspection AdditionOperationOnArraysInspection */
                $allExistGroups += (array)$basketItem['DISCOUNT_GROUPS'];
            }
            $max = max(array_keys($allExistGroups));
            foreach ($basketPart as &$basketItem) {
                $newGroups = [];
                /** @noinspection ForeachSourceInspection */
                foreach ($basketItem['DISCOUNT_GROUPS'] as $k => $v) {
                    $newGroups[$k + $max] = $v;
                }
                $basketItem['DISCOUNT_GROUPS'] = $newGroups;
            }
            unset($basketItem);
        }
        return $basketPart;
    }
}