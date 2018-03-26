<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\Base\Orders;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class DeliveryAddress
 *
 * @package FourPaws\SapBundle\Dto\Base\Orders
 */
class DeliveryAddress
{
    /**
     * Страна
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("COUNTRY")
     *
     * @var string
     */
    protected $countryCode = 'RU';

    /**
     * Код региона
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("REGION")
     *
     * @var string
     */
    protected $regionCode = '';

    /**
     * Почтовый индекс
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("POSTCODE")
     *
     * @var string
     */
    protected $postCode = '';

    /**
     * Название города
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("CITY")
     *
     * @var string
     */
    protected $cityName = '';

    /**
     * Название улицы
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("STREET")
     *
     * @var string
     */
    protected $streetName = '';

    /**
     * Префикс улицы
     * ул, пр-т, б-р и т.д.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("STREETABBR")
     *
     * @var string
     */
    protected $streetPrefix = '';

    /**
     * Номер дома
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("HOUSE_NUM")
     *
     * @var string
     */
    protected $house = '';

    /**
     * Корпус дома
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("HOUSE_KORPUS")
     *
     * @var string
     */
    protected $housing = '';

    /**
     * Строение
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("HOUSE_STR")
     *
     * @var string
     */
    protected $building = '';

    /**
     * Владение
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("HOUSE_VLAD")
     *
     * @var string
     */
    protected $ownerShip = '';

    /**
     * Этаж
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("FLOOR")
     *
     * @var string
     */
    protected $floor = '';

    /**
     * Квартира/комната/номер офиса
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ROOMNUMBER")
     *
     * @var string
     */
    protected $roomNumber = '';

    /**
     * Номер пункта выдачи заказов подрядчика
     * Поле должно быть заполнено, если выбран способ получения заказа 07 и тип доставки внешним подрядчиком ТТ.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("SPTERMINALCODE")
     *
     * @var string
     */
    protected $deliveryPointCode = '';
    
    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }
    
    /**
     * @param string $countryCode
     *
     * @return DeliveryAddress
     */
    public function setCountryCode(string $countryCode): DeliveryAddress
    {
        $this->countryCode = $countryCode;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getRegionCode(): string
    {
        return $this->regionCode;
    }
    
    /**
     * @param string $regionCode
     *
     * @return DeliveryAddress
     */
    public function setRegionCode(string $regionCode): DeliveryAddress
    {
        $this->regionCode = $regionCode;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPostCode(): string
    {
        return $this->postCode;
    }
    
    /**
     * @param string $postCode
     *
     * @return DeliveryAddress
     */
    public function setPostCode(string $postCode): DeliveryAddress
    {
        $this->postCode = $postCode;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getCityName(): string
    {
        return $this->cityName;
    }
    
    /**
     * @param string $cityName
     *
     * @return DeliveryAddress
     */
    public function setCityName(string $cityName): DeliveryAddress
    {
        $this->cityName = $cityName;
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
     *
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
    public function getStreetPrefix(): string
    {
        return $this->streetPrefix;
    }
    
    /**
     * @param string $streetPrefix
     *
     * @return DeliveryAddress
     */
    public function setStreetPrefix(string $streetPrefix): DeliveryAddress
    {
        $this->streetPrefix = $streetPrefix;
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
     *
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
    public function getHousing(): string
    {
        return $this->housing;
    }
    
    /**
     * @param string $housing
     *
     * @return DeliveryAddress
     */
    public function setHousing(string $housing): DeliveryAddress
    {
        $this->housing = $housing;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getBuilding(): string
    {
        return $this->building;
    }
    
    /**
     * @param string $building
     *
     * @return DeliveryAddress
     */
    public function setBuilding(string $building): DeliveryAddress
    {
        $this->building = $building;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getOwnerShip(): string
    {
        return $this->ownerShip;
    }
    
    /**
     * @param string $ownerShip
     *
     * @return DeliveryAddress
     */
    public function setOwnerShip(string $ownerShip): DeliveryAddress
    {
        $this->ownerShip = $ownerShip;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getFloor(): string
    {
        return $this->floor;
    }
    
    /**
     * @param string $floor
     *
     * @return DeliveryAddress
     */
    public function setFloor(string $floor): DeliveryAddress
    {
        $this->floor = $floor;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getRoomNumber(): string
    {
        return $this->roomNumber;
    }
    
    /**
     * @param string $roomNumber
     *
     * @return DeliveryAddress
     */
    public function setRoomNumber(string $roomNumber): DeliveryAddress
    {
        $this->roomNumber = $roomNumber;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getDeliveryPointCode(): string
    {
        return $this->deliveryPointCode;
    }
    
    /**
     * @param string $deliveryPointCode
     *
     * @return DeliveryAddress
     */
    public function setDeliveryPointCode(string $deliveryPointCode): DeliveryAddress
    {
        $this->deliveryPointCode = $deliveryPointCode;
        return $this;
    }
}
