<?php
/**
 * Created by PhpStorm.
 * Date: 14.03.2018
 * Time: 22:47
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils;


/**
 * Interface AdderInterface
 * @package FourPaws\SaleBundle\Discount\Utils
 */
interface AdderInterface
{
    public function processOrder(): void;
}