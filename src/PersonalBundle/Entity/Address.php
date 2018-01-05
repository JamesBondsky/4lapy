<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Entity;

use FourPaws\AppBundle\Entity\BaseEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class Address extends BaseEntity
{
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_NAME")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $name = '';
    
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_USER_ID")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     * @Assert\NotBlank(groups={"create","read","update","delete"})
     */
    protected $userId;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_CITY")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     * @Assert\NotBlank(groups={"create","read","update","delete"})
     */
    protected $city = '';
    
    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_CITY_LOCATION")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $cityLocation = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_STREET")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     * @Assert\NotBlank(groups={"create","read","update","delete"})
     */
    protected $street = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_HOUSE")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     * @Assert\NotBlank(groups={""create",read","update","delete"})
     */
    protected $house = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_HOUSING")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $housing = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_ENTRANCE")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $entrance = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_FLOOR")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $floor = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_FLAT")
     * @Serializer\Groups(groups={"read"})
     */
    protected $flat = '';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_INTERCOM_CODE")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $intercomCode = '';
    
    /**
     * @var bool
     * @Serializer\AccessType(type="public_method")
     * @Serializer\Accessor(getter="getRawMain", setter="setRawMain")
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_MAIN")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $main = false;
    
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name ?? '';
    }
    
    /**
     * @param string $name
     *
     * @return Address
     */
    public function setName(string $name) : Address
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * @param string $main
     *
     * @return Address
     */
    public function setRawMain(string $main) : Address
    {
        return $this->setMain($main === static::BITRIX_TRUE);
    }
    
    /**
     * @return string
     */
    public function getRawMain() : string
    {
        return $this->isMain() ? static::BITRIX_TRUE : static::BITRIX_FALSE;
    }
    
    /**
     * @return bool
     */
    public function isMain() : bool
    {
        return $this->main;
    }
    
    /**
     * @param bool $main
     *
     * @return Address
     */
    public function setMain(bool $main) : Address
    {
        $this->main = $main;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userId;
    }
    
    /**
     * @param int $userId
     *
     * @return Address
     */
    public function setUserId(int $userId) : Address
    {
        $this->userId = $userId;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getFullAddress() : string
    {
        $housing = '';
        if (!empty($this->getHousing())) {
            $housing .= ', корпус' . $this->getHousing();
        }
        $entrance = '';
        if (!empty($this->getEntrance())) {
            $entrance .= ', подъезд' . $this->getEntrance();
        }
        $floor = '';
        if (!empty($this->getFloor())) {
            $floor .= ', этаж' . $this->getFloor();
        }
        $flat = '';
        if (!empty($this->getFlat())) {
            $flat .= ', кв. ' . $this->getFlat();
        }
        $intercomCode = '';
        if (!empty($this->getIntercomCode())) {
            $intercomCode .= ', код домофона' . $this->getIntercomCode();
        }
        $house = ',д. ' . $this->getHouse();
        
        $res =
            $this->getStreet() . ' ул.' . $house . $housing . $entrance . $floor . $flat . $intercomCode . ' '
            . $this->getCity();
        
        return $res;
    }
    
    /**
     * @return string
     */
    public function getHousing() : string
    {
        return $this->housing ?? '';
    }
    
    /**
     * @param string $housing
     *
     * @return Address
     */
    public function setHousing(string $housing) : Address
    {
        $this->housing = $housing;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getEntrance() : string
    {
        return $this->entrance ?? '';
    }
    
    /**
     * @param string $entrance
     *
     * @return Address
     */
    public function setEntrance(string $entrance) : Address
    {
        $this->entrance = $entrance;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getFloor() : string
    {
        return $this->floor ?? '';
    }
    
    /**
     * @param string $floor
     *
     * @return Address
     */
    public function setFloor(string $floor) : Address
    {
        $this->floor = $floor;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getFlat() : string
    {
        return $this->flat ?? '';
    }
    
    /**
     * @param string $flat
     *
     * @return Address
     */
    public function setFlat(string $flat) : Address
    {
        $this->flat = $flat;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getIntercomCode() : string
    {
        return $this->intercomCode ?? '';
    }
    
    /**
     * @param string $intercomCode
     *
     * @return Address
     */
    public function setIntercomCode(string $intercomCode) : Address
    {
        $this->intercomCode = $intercomCode;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getHouse() : string
    {
        return $this->house;
    }
    
    /**
     * @param string $house
     *
     * @return Address
     */
    public function setHouse(string $house) : Address
    {
        $this->house = $house;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getStreet() : string
    {
        return $this->street;
    }
    
    /**
     * @param string $street
     *
     * @return Address
     */
    public function setStreet(string $street) : Address
    {
        $this->street = $street;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getCity() : string
    {
        return $this->city;
    }
    
    /**
     * @param string $city
     *
     * @return Address
     */
    public function setCity(string $city) : Address
    {
        $this->city = $city;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getCityLocation() : int
    {
        return $this->cityLocation ?? 0;
    }
    
    /**
     * @param int $cityLocation
     *
     * @return Address
     */
    public function setCityLocation(int $cityLocation) : Address
    {
        $this->cityLocation = $cityLocation;
        
        return $this;
    }
}
