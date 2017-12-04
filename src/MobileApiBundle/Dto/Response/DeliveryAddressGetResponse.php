<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress;
use JMS\Serializer\Annotation as Serializer;

class DeliveryAddressGetResponse
{
    /**
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress>")
     * @Serializer\SerializedName("address")
     * @var DeliveryAddress[]
     */
    protected $address = [];

    /**
     * @return DeliveryAddress[]
     */
    public function getAddress(): array
    {
        return $this->address;
    }

    /**
     * @param DeliveryAddress[] $address
     * @return DeliveryAddressGetResponse
     */
    public function setAddress(array $address): DeliveryAddressGetResponse
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @param DeliveryAddress $address
     * @return $this
     */
    public function addAddress(DeliveryAddress $address)
    {
        $this->address[$address->getId()] = $address;
        return $this;
    }

    /**
     * @param DeliveryAddress $address
     * @return $this
     */
    public function removeAddress(DeliveryAddress $address)
    {
        unset($this->address[$address->getId()]);
        return $this;
    }
}
