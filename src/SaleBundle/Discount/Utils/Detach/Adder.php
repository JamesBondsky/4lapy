<?php
/**
 * Created by PhpStorm.
 * Date: 14.03.2018
 * Time: 23:00
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils\Detach;


use Bitrix\Sale\Order;
use FourPaws\SaleBundle\Discount\Utils\AdderInterface;
use FourPaws\SaleBundle\Service\BasketService;

/**
 * Class Adder
 * @package FourPaws\SaleBundle\Discount\Utils\Detach
 */
class Adder implements AdderInterface
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
     * Adder constructor.
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
        if (!$discount = $this->order->getDiscount()) {
            return;
        }
        dump($discount->getApplyResult());
    }
}