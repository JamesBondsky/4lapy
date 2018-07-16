<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\Fiscalization;

use JMS\Serializer\Annotation as Serializer;

class Fiscalization
{
    /**
     * @var Fiscal
     *
     * @Serializer\SerializedName("fiscal")
     * @Serializer\Type("FourPaws\SaleBundle\Dto\Fiscalization\Fiscal")
     */
    protected $fiscal;

    /**
     * @return Fiscal
     */
    public function getFiscal(): Fiscal
    {
        return $this->fiscal;
    }

    /**
     * @param Fiscal $fiscal
     * @return Fiscalization
     */
    public function setFiscal(Fiscal $fiscal): Fiscalization
    {
        $this->fiscal = $fiscal;
        return $this;
    }
}