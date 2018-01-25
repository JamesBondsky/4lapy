<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\OrderParameter;

class UserCartOrderRequest
{
    /**
     * Содержит исходные входящие данные корзины.
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\OrderParameter")
     * @Serializer\SerializedName("cart_param")
     * @var OrderParameter
     */
    protected $cartParam;

    /**
     * @return OrderParameter
     */
    public function getCartParam(): OrderParameter
    {
        return $this->cartParam;
    }

    /**
     * @param OrderParameter $cartParam
     *
     * @return UserCartOrderRequest
     */
    public function setCartParam(OrderParameter $cartParam): UserCartOrderRequest
    {
        $this->cartParam = $cartParam;
        return $this;
    }
}
