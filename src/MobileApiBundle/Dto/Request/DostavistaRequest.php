<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class DostavistaRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\SerializedName("city")
     * @Serializer\Type("string")
     * @var string
     */
    protected $city = '';
    /**
     * @Serializer\SerializedName("street")
     * @Serializer\Type("string")
     * @var string
     */
    protected $street = '';
    /**
     * @Serializer\SerializedName("house")
     * @Serializer\Type("string")
     * @var string
     */
    protected $house = '';
    /**
     * @Serializer\SerializedName("building")
     * @Serializer\Type("string")
     * @var string
     */
    protected $building = '';
    /**
     * @Serializer\SerializedName("addressId")
     * @Serializer\Type("string")
     * @var string
     */
    protected $addressId = '';

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function getHouse(): string
    {
        return $this->house;
    }

    /**
     * @return string
     */
    public function getBuilding(): string
    {
        return $this->building;
    }

    /**
     * @return string
     */
    public function getAddressId(): string
    {
        return $this->addressId;
    }
}
