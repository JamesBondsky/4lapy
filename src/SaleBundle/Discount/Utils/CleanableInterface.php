<?php
/**
 * Created by PhpStorm.
 * Date: 30.01.2018
 * Time: 14:18
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils;


use Bitrix\Sale\Basket;

/**
 * Interface CleanableInterface
 * @package FourPaws\SaleBundle\Discount\Cleaner
 */
interface CleanableInterface
{
    /**
     *
     *
     * @param Basket $basket
     *
     * @return mixed
     */
    public static function Clean(Basket $basket);
}