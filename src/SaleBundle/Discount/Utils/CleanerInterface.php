<?php
/**
 * Created by PhpStorm.
 * Date: 14.03.2018
 * Time: 22:53
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils;


/**
 * Interface CleanerInterface
 * @package FourPaws\SaleBundle\Discount\Utils
 */
interface CleanerInterface
{
    public function processOrder(): void;
}