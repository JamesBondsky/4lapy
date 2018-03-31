<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Entity;

use Bitrix\Sale\Order;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;

class OrderSplitResult
{
    /** @var Order */
    protected $order;

    /** @var OrderStorage */
    protected $orderStorage;

    /** @var CalculationResultInterface */
    protected $delivery;

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

    /**
     * @return CalculationResultInterface
     */
    public function getDelivery(): CalculationResultInterface
    {
        return $this->delivery;
    }

    /**
     * @param CalculationResultInterface $delivery
     * @return OrderSplitResult
     */
    public function setDelivery(CalculationResultInterface $delivery): OrderSplitResult
    {
        $this->delivery = $delivery;
        return $this;
    }
}
