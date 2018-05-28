<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\Out\Orders;

use FourPaws\SapBundle\Dto\Base\Orders\DeliveryAddress as DeliveryAddressBase;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class DeliveryAddress
 *
 * @package FourPaws\SapBundle\Dto\Out\Orders
 */
class DeliveryAddress extends DeliveryAddressBase
{
    /**
     * Код магазина
     *
     * @var string
     * @Serializer\Exclude()
     */
    protected $deliveryPlaceCode = '';

    /**
     * @return string
     */
    public function getDeliveryPlaceCode(): string
    {
        return $this->deliveryPlaceCode;
    }

    /**
     * @param string $deliveryPlaceCode
     * @return DeliveryAddress
     */
    public function setDeliveryPlaceCode(string $deliveryPlaceCode): DeliveryAddress
    {
        $this->deliveryPlaceCode = $deliveryPlaceCode;
        return $this;
    }

    public function __toString(): string
    {
        return \implode(
            ', ',
            \array_filter(
                [
                    parent::__toString(),
                    $this->deliveryPlaceCode,
                ]
            )
        );
    }
}
