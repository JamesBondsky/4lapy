<?php

namespace FourPaws\MobileApiBundle\Dto\Request;


use FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class DeliveryAddressPostPutRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type("FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress")
     * @Serializer\SerializedName("address")
     * @var DeliveryAddress
     */
    protected $address;

    /**
     * @return DeliveryAddress
     */
    public function getAddress(): DeliveryAddress
    {
        return $this->address;
    }

    /**
     * @param DeliveryAddress $address
     * @return DeliveryAddressPostPutRequest
     */
    public function setAddress(DeliveryAddress $address): DeliveryAddressPostPutRequest
    {
        $this->address = $address;
        return $this;
    }
}