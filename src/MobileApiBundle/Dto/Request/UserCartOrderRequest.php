<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\OrderParameter;
use Symfony\Component\Validator\Constraints as Assert;

class UserCartOrderRequest
{
    /**
     * Содержит исходные входящие данные корзины.
     * @Assert\Valid()
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
