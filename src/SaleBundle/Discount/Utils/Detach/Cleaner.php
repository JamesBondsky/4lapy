<?php
/**
 * Created by PhpStorm.
 * Date: 14.03.2018
 * Time: 23:01
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils\Detach;

use FourPaws\SaleBundle\Discount\Utils\BaseDiscountPostHandler;
use FourPaws\SaleBundle\Discount\Utils\CleanerInterface;

/**
 * Class Cleaner
 * @package FourPaws\SaleBundle\Discount\Utils\Detach
 */
class Cleaner extends BaseDiscountPostHandler implements CleanerInterface
{
    public function processOrder(): void
    {
        // TODO: Implement processOrder() method.
    }
}