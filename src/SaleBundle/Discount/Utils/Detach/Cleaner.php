<?php
/**
 * Created by PhpStorm.
 * Date: 14.03.2018
 * Time: 23:01
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils\Detach;


use Bitrix\Sale\Order;
use FourPaws\SaleBundle\Discount\Utils\CleanerInterface;
use FourPaws\SaleBundle\Service\BasketService;

/**
 * Class Cleaner
 * @package FourPaws\SaleBundle\Discount\Utils\Detach
 */
class Cleaner implements CleanerInterface
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
     * Cleaner constructor.
     *
     * @param Order $order
     * @param BasketService $basketService
     */
    public function __construct(Order $order, BasketService $basketService)
    {
        $this->order = $order;
        $this->basketService = $basketService;
    }
    public function processOrder(): void
    {
        // TODO: Implement processOrder() method.
    }
}