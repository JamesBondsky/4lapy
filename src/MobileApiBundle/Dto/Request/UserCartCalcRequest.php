<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\OrderParameter;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class UserCartCalcRequest
{
    /**
     * ОбъектПараметрЗаказа
     * @Assert\Valid()
     * @Serializer\SerializedName("cart_param")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\OrderParameter")
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
     * @return UserCartCalcRequest
     */
    public function setCartParam(OrderParameter $cartParam): UserCartCalcRequest
    {
        $this->cartParam = $cartParam;
        return $this;
    }
}
