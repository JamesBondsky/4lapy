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
     * Регион
     *
     * @var string
     * @Serializer\Exclude()
     */
    protected $region = '';

    /**
     * Район
     *
     * @var string
     * @Serializer\Exclude()
     */
    protected $area = '';

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
    public function getArea(): string
    {
        return $this->area;
    }

    /**
     * @param string $area
     * @return DeliveryAddress
     */
    public function setArea(string $area): DeliveryAddress
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * @param string $region
     * @return DeliveryAddress
     */
    public function setRegion(string $region): DeliveryAddress
    {
        $this->region = $region;

        return $this;
    }

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
                    $this->region,
                    $this->area,
                    parent::__toString(),
                    $this->deliveryPlaceCode,
                ]
            )
        );
    }
}
