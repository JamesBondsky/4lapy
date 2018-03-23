<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

/**
 * @todo assert
 * ОбъектАдресДоставки
 * Class DeliveryAddress
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class DeliveryAddress
{
    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     * @var string
     */
    protected $id;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     * @var string
     */
    protected $title;

    /**
     * @Serializer\SerializedName("city")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\City")
     * @var null|City
     */
    protected $city;

    /**
     * @Serializer\SerializedName("street_name")
     * @Serializer\Type("string")
     * @var string
     */
    protected $streetName;

    /**
     * @Serializer\SerializedName("house")
     * @Serializer\Type("string")
     * @var string
     */
    protected $house;

    /**
     * @Serializer\SerializedName("flat")
     * @Serializer\Type("string")
     * @var string
     */
    protected $flat;

    /**
     * @Serializer\SerializedName("details")
     * @Serializer\Type("string")
     * @var string
     */
    protected $details;

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
    public function getCity(): City
    {
        return $this->city;
    }

    /**
     * @param null|City $city
     * @return DeliveryAddress
     */
    public function setCity(City $city): DeliveryAddress
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
     * @return string
     */
    public function getHouse(): string
    {
        return $this->house;
    }

    /**
     * @param string $house
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
        return $this->flat;
    }

    /**
     * @param string $flat
     * @return DeliveryAddress
     */
    public function setFlat(string $flat): DeliveryAddress
    {
        $this->flat = $flat;
        return $this;
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return $this->details;
    }

    /**
     * @param string $details
     * @return DeliveryAddress
     */
    public function setDetails(string $details): DeliveryAddress
    {
        $this->details = $details;
        return $this;
    }
}
