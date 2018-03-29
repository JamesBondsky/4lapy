<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Entity;

use Bitrix\Sale\Order;

class OrderSplitResult
{
    /** @var Order */
    protected $order;

    /** @var OrderStorage */
    protected $orderStorage;

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @param Order $order
     * @return OrderSplitResult
     */
    public function setOrder(Order $order): OrderSplitResult
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return OrderStorage
     */
    public function getOrderStorage(): OrderStorage
    {
        return $this->orderStorage;
    }

    /**
     * @param OrderStorage $orderStorage
     * @return OrderSplitResult
     */
    public function setOrderStorage(OrderStorage $orderStorage): OrderSplitResult
    {
        $this->orderStorage = $orderStorage;
        return $this;
    }
}
