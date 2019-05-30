<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\LocationBundle\Entity;


class Address
{
    /**
     * @var string
     */
    protected $location = '';

    /**
     * @var string
     */
    protected $zipCode = '';

    /**
     * @var string
     */
    protected $region = '';

    /**
     * @var string
     */
    protected $area = '';

    /**
     * @var string
     */
    protected $city = '';

    /**
     * @var string
     */
    protected $street = '';

    /**
     * @var string
     */
    protected $streetPrefix = '';

    /**
     * @var string
     */
    protected $house = '';

    /**
     * @var string
     */
    protected $housing = '';

    /**
     * @var string
     */
    protected $entrance = '';

    /**
     * @var string
     */
    protected $intercomCode = '';

    /**
     * @var string
     */
    protected $floor = '';

    /**
     * @var string
     */
    protected $flat = '';

    /**
     * @var bool
     */
    protected $valid = false;

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     *
     * @return Address
     */
    public function setLocation(string $location): Address
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return string
     */
    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     *
     * @return Address
     */
    public function setZipCode(string $zipCode): Address
    {
        $this->zipCode = $zipCode;
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
     * @return Address
     */
    public function setRegion(string $region): Address
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return string
     */
    public function getArea(): string
    {
        return $this->area;
    }

    /**
     * @param string $area
     * @return Address
     */
    public function setArea(string $area): Address
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return Address
     */
    public function setCity(string $city): Address
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     *
     * @return Address
     */
    public function setStreet(string $street): Address
    {
        $this->street = $street;
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
     * @return Address
     */
    public function setStreetPrefix(string $streetPrefix): Address
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
     * @return Address
     */
    public function setHouse(string $house): Address
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
     * @return Address
     */
    public function setHousing(string $housing): Address
    {
        $this->housing = $housing;
        return $this;
    }

    /**
     * @return string
     */
    public function getEntrance(): string
    {
        return $this->entrance;
    }

    /**
     * @param string $entrance
     *
     * @return Address
     */
    public function setEntrance(string $entrance): Address
    {
        $this->entrance = $entrance;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntercomCode(): string
    {
        return $this->intercomCode;
    }

    /**
     * @param string $intercomCode
     *
     * @return Address
     */
    public function setIntercomCode(string $intercomCode): Address
    {
        $this->intercomCode = $intercomCode;
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
     * @return Address
     */
    public function setFloor(string $floor): Address
    {
        $this->floor = $floor;
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
     *
     * @return Address
     */
    public function setFlat(string $flat): Address
    {
        $this->flat = $flat;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @param bool $valid
     *
     * @return Address
     */
    public function setValid(bool $valid): Address
    {
        $this->valid = $valid;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $map = [
            ['value' => $this->region, 'prefix' => ''],
            ['value' => $this->area, 'prefix' => ''],
            ['value' => $this->city, 'prefix' => ''],
            ['value' => $this->street, 'prefix' => $this->streetPrefix ?: ''],
            ['value' => $this->house, 'prefix' => 'дом'],
            ['value' => $this->housing, 'prefix' => 'корпус'],
            ['value' => $this->entrance, 'prefix' => 'подъезд'],
            ['value' => $this->floor, 'prefix' => 'этаж'],
            ['value' => $this->flat, 'prefix' => 'кв.'],
            ['value' => $this->intercomCode, 'prefix' => 'код домофона']
        ];

        $result = \array_filter(\array_map(function ($item) {
            $result = '';
            if ($item['value']) {
                $result = $item['prefix'] ? $item['prefix'] . ' ' . $item['value'] : $item['value'];
            }
            return $result;
        }, $map));

        return implode(', ', $result);
    }

    /**
     * @return string
     */
    public function toStringExt(): string
    {
        $map = [
            ['value' => $this->region, 'prefix' => ''],
            ['value' => $this->area, 'prefix' => ''],
            ['value' => $this->city, 'prefix' => ''],
            ['value' => $this->street, 'prefix' => $this->streetPrefix ?: ''],
            ['value' => $this->house, 'prefix' => 'д'],
            ['value' => preg_replace('/[^0-9a-zA-Z_\s-]/', '', $this->housing), 'prefix' => 'стр'],
            ['value' => $this->entrance, 'prefix' => 'подъезд'],
            ['value' => $this->floor, 'prefix' => 'этаж'],
            ['value' => $this->flat, 'prefix' => 'кв.'],
            ['value' => $this->intercomCode, 'prefix' => 'код домофона']
        ];

        $result = \array_filter(\array_map(function ($item) {
            $result = '';
            if ($item['value']) {
                $result = $item['prefix'] ? $item['prefix'] . ' ' . $item['value'] : $item['value'];
            }
            return $result;
        }, $map));

        return implode(', ', $result);
    }
}
