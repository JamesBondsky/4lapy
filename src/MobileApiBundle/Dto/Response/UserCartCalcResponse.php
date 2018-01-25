<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\OrderCalculate;
use JMS\Serializer\Annotation as Serializer;

class UserCartCalcResponse
{
    /**
     * ОбъектРасчетЗаказа
     * @Serializer\SerializedName("cart_calc")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\OrderCalculate")
     * @var OrderCalculate
     */
    protected $cartCalc;

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
     * @return UserCartCalcResponse
     */
    public function setCartCalc(OrderCalculate $cartCalc): UserCartCalcResponse
    {
        $this->cartCalc = $cartCalc;
        return $this;
    }
}
