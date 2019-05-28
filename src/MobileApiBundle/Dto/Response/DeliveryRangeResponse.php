<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\DeliveryTime;

class DeliveryRangeResponse
{
    /**
     * @var DeliveryTime[]
     */
    protected $ranges = [];

    /**
     * @param DeliveryTime[] $ranges
     */
    public function __construct(array $ranges)
    {
        $this->setRanges($ranges);
    }

    /**
     * @return DeliveryTime[]
     */
    public function getRanges(): array
    {
        return $this->ranges;
    }

    /**
     * @param DeliveryTime[] $ranges
     * @return DeliveryRangeResponse
     */
    public function setRanges(array $ranges): DeliveryRangeResponse
    {
        $this->ranges = $ranges;
        return $this;
    }
}
