<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo    assert
 * ОбъектАдресДоставки
 * Class DeliveryAddress
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class DeliveryAddress
{
    /**
     * @Assert\GreaterThan(value="0", groups={"update","read","delete"})
     * @Serializer\Groups(groups={"update","read","delete"})
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     * @var string
     */
    protected $id;

    /**
     * @Assert\NotBlank()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     * @var string
     */
    protected $title;

    /**
     * @Assert\Valid()
     * @Serializer\SerializedName("city")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\City")
     * @var null|City
     */
    protected $city;

    /**
     * @Assert\NotBlank()
     * @Serializer\SerializedName("street_name")
     * @Serializer\Type("string")
     * @var string
     */
    protected $streetName;

    /**
     * @Assert\NotBlank()
     * @Serializer\SerializedName("house")
     * @Serializer\Type("string")
     * @var null|string
     */
    protected $house;

    /**
     * @Serializer\SerializedName("flat")
     * @Serializer\Type("string")
     * @var null|string
     */
    protected $flat;

    /**
     * @Serializer\SerializedName("details")
     * @Serializer\Type("string")
     * @var null|string
     */
    protected $details;

    /**
     * @Serializer\SerializedName("str")
     * @Serializer\Type("string")
     * @var null|string
     */
    protected $building;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return DeliveryAddress
     */
    public function setId(int $id): DeliveryAddress
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return DeliveryAddress
     */
    public function setTitle(string $title): DeliveryAddress
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return null|City
     */
    public function getCity(): ?City
    {
        return $this->city;
    }

    /**
     * @param null|City $city
     * @return DeliveryAddress
     */
    public function setCity(?City $city): DeliveryAddress
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreetName(): string
    {
        return $this->streetName;
    }

    /**
     * @param string $streetName
     * @return DeliveryAddress
     */
    public function setStreetName(string $streetName): DeliveryAddress
    {
        $this->streetName = $streetName;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getHouse(): string
    {
        return $this->house;
    }

    /**
     * @param null|string $house
     * @return DeliveryAddress
     */
    public function setHouse(string $house): DeliveryAddress
    {
        $this->house = $house;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlat(): string
    {
        return $this->flat ?? '';
    }

    /**
     * @param null|string $flat
     * @return DeliveryAddress
     */
    public function setFlat(string $flat): DeliveryAddress
    {
        $this->flat = $flat;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDetails(): string
    {
        return $this->details ?: '';
    }

    /**
     * @param null|string $details
     * @return DeliveryAddress
     */
    public function setDetails(string $details): DeliveryAddress
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @return string
     */
    public function getBuilding(): string
    {
        return $this->building ?? '';
    }

    /**
     * @param null|string $building
     * @return DeliveryAddress
     */
    public function setBuilding(string $building): DeliveryAddress
    {
        $this->building = $building;
        return $this;
    }
}
