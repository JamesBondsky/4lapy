<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\OrderCalculate;
use FourPaws\MobileApiBundle\Dto\Object\OrderParameter;

class PostUserCartResponse
{
    /**
     * Содержит исходные входящие данные корзины.
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\OrderParameter")
     * @Serializer\SerializedName("cart_param")
     * @var OrderParameter
     */
    protected $cartParam;

    /**
     * Содержит рассчитанные сервером (выходящие) параметры корзины.
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\OrderCalculate")
     * @Serializer\SerializedName("cart_calc")
     * @var OrderCalculate
     */
    protected $cartCalc;

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
     * @return PostUserCartResponse
     */
    public function setCartParam(OrderParameter $cartParam): PostUserCartResponse
    {
        $this->cartParam = $cartParam;
        return $this;
    }

    /**
     * @return OrderCalculate
     */
    public function getCartCalc(): OrderCalculate
    {
        return $this->cartCalc;
    }

    /**
     * @param OrderCalculate $cartCalc
     *
     * @return PostUserCartResponse
     */
    public function setCartCalc(OrderCalculate $cartCalc): PostUserCartResponse
    {
        $this->cartCalc = $cartCalc;
        return $this;
    }
}
