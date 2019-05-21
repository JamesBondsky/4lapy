<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Order;
use JMS\Serializer\Annotation as Serializer;

class UserCartOrderResponse
{
    /**
     * ОбъектЗаказ
     * @Serializer\SerializedName("cart_order")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Order>")
     * @var Order[]
     */
    protected $cartOrder;

    /**
     * @return Order[]
     */
    public function getCartOrder(): array
    {
        return $this->cartOrder;
    }

    /**
     * @param Order[] $cartOrder
     *
     * @return UserCartOrderResponse
     */
    public function setCartOrder(array $cartOrder): UserCartOrderResponse
    {
        $this->cartOrder = $cartOrder;
        return $this;
    }
}
