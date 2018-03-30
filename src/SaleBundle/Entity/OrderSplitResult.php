<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Entity;

use Bitrix\Sale\Order;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;

class OrderSplitResult
{
    /** @var Order */
    protected $order;

    /** @var OrderStorage */
    protected $orderStorage;

    /** @var StockResultCollection */
    protected $stockResult;

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
     * @return StockResultCollection
     */
    public function getStockResult(): StockResultCollection
    {
        return $this->stockResult;
    }

    /**
     * @param StockResultCollection $stockResult
     * @return OrderSplitResult
     */
    public function setStockResult(StockResultCollection $stockResult): OrderSplitResult
    {
        $this->stockResult = $stockResult;
        return $this;
    }
}
