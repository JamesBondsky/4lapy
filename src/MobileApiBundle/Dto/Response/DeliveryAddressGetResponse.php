<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Response;

use Doctrine\Common\Collections\Collection;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress;
use JMS\Serializer\Annotation as Serializer;

class DeliveryAddressGetResponse
{
    /**
     * @Serializer\Type("ArrayCollection<FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress>")
     * @Serializer\SerializedName("address")
     * @var DeliveryAddress[]
     */
    protected $addresses;

    public function __construct(Collection $collection)
    {
        $this->addresses = $collection;
    }

    /**
     * @return Collection|DeliveryAddress[]
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    /**
     * @param Collection|DeliveryAddress[] $addresses
     *
     * @return DeliveryAddressGetResponse
     */
    public function setAddresses(Collection $addresses): DeliveryAddressGetResponse
    {
        $this->addresses = $addresses;
        return $this;
    }

    /**
     * @param DeliveryAddress $address
     *
     * @return bool
     */
    public function addAddress(DeliveryAddress $address): bool
    {
        return $this->getAddresses()->add($address);
    }

    /**
     * @param DeliveryAddress $address
     *
     * @return bool
     */
    public function removeAddress(DeliveryAddress $address): bool
    {
        return $this->getAddresses()->removeElement($address);
    }
}
