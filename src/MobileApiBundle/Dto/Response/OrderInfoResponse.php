<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Order;
use JMS\Serializer\Annotation as Serializer;

class OrderInfoResponse
{
    /**
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Order")
     * @var Order
     */
    protected $order;

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @param Order $order
     *
     * @return OrderInfoResponse
     */
    public function setOrder(Order $order): OrderInfoResponse
    {
        $this->order = $order;
        return $this;
    }
}
