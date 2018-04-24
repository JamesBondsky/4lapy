<?php
/**
 * Created by PhpStorm.
 * Date: 15.03.2018
 * Time: 13:17
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils;

use Bitrix\Sale\Order;
use FourPaws\SaleBundle\Service\BasketService;


/**
 * Class BaseDiscountPostHandler
 * @package FourPaws\SaleBundle\Discount\Utils
 */
abstract class BaseDiscountPostHandler
{
    /**
     * @var Order
     */
    protected $order;
    /**
     * @var BasketService
     */
    protected $basketService;


    /**
     * BaseDiscountPostHandler constructor.
     *
     * @param Order $order
     * @param BasketService $basketService
     */
    public function __construct(Order $order, BasketService $basketService)
    {
        $this->order = $order;
        $this->basketService = $basketService;
    }

    abstract public function processOrder(): void;
}
