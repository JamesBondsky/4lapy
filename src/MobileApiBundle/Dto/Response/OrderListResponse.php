<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Order;
use JMS\Serializer\Annotation as Serializer;

class OrderListResponse
{
    /**
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Order>")
     * @var Order[]
     */
    protected $orderList = [];

    /**
     * @return Order[]
     */
    public function getOrderList(): array
    {
        return $this->orderList;
    }

    /**
     * @param Order[] $orderList
     *
     * @return OrderListResponse
     */
    public function setOrderList(array $orderList): OrderListResponse
    {
        $this->orderList = $orderList;
        return $this;
    }
}
