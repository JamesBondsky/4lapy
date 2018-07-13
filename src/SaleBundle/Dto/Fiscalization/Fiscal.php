<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\Fiscalization;

use JMS\Serializer\Annotation as Serializer;

class Fiscal
{
    /**
     * @var OrderBundle
     *
     * @Serializer\SerializedName("orderBundle")
     * @Serializer\Type("FourPaws\SaleBundle\Dto\Fiscalization\OrderBundle")
     */
    protected $orderBundle;

    /**
     * @var int
     *
     * @Serializer\SerializedName("taxSystem")
     * @Serializer\Type("int")
     */
    protected $taxSystem;

    /**
     * @return OrderBundle
     */
    public function getOrderBundle(): OrderBundle
    {
        return $this->orderBundle;
    }

    /**
     * @param OrderBundle $orderBundle
     * @return Fiscal
     */
    public function setOrderBundle(OrderBundle $orderBundle): Fiscal
    {
        $this->orderBundle = $orderBundle;
        return $this;
    }

    /**
     * @return int
     */
    public function getTaxSystem(): int
    {
        return $this->taxSystem;
    }

    /**
     * @param int $taxSystem
     * @return Fiscal
     */
    public function setTaxSystem(int $taxSystem): Fiscal
    {
        $this->taxSystem = $taxSystem;
        return $this;
    }
}