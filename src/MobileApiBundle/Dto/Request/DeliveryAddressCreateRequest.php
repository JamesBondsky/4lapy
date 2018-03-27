<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class DeliveryAddressCreateRequest implements PostRequest, SimpleUnserializeRequest, CreateRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\Valid()
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
     * @return DeliveryAddressCreateRequest
     */
    public function setAddress(DeliveryAddress $address): DeliveryAddressCreateRequest
    {
        $this->address = $address;
        return $this;
    }
}
