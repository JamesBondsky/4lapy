<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Entity;

use FourPaws\App\Application;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\LocationService;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class Address extends BaseEntity
{
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_NAME")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $name = '';

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_USER_ID")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $userId;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_CITY")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $city = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_CITY_LOCATION")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $cityLocation = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_STREET")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $street = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_HOUSE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $house = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_HOUSING")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $housing = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_ENTRANCE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $entrance = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_FLOOR")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $floor = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_FLAT")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $flat = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_INTERCOM_CODE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $intercomCode = '';

    /**
     * @var string
     * @Assert\Length(min="0", max="1024")
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_DETAILS")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $details = '';

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("UF_MAIN")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $main = false;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? '';
    }

    /**
     * @param string $name
     *
     * @return Address
     */
    public function setName(string $name): Address
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMain(): bool
    {
        return $this->main ?? false;
    }

    /**
     * @param bool $main
     *
     * @return Address
     */
    public function setMain(bool $main): Address
    {
        $this->main = $main;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId ?? 0;
    }

    /**
     * @param int $userId
     *
     * @return Address
     */
    public function setUserId(int $userId): Address
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullAddress(): string
    {
        $housing = '';
        if (!empty($this->getHousing())) {
            $housing .= ', корпус ' . $this->getHousing();
        }
        $entrance = '';
        if (!empty($this->getEntrance())) {
            $entrance .= ', подъезд ' . $this->getEntrance();
        }
        $floor = '';
        if (!empty($this->getFloor())) {
            $floor .= ', этаж ' . $this->getFloor();
        }
        $flat = '';
        if (!empty($this->getFlat())) {
            $flat .= ', кв. ' . $this->getFlat();
        }
        $intercomCode = '';
        if (!empty($this->getIntercomCode())) {
            $intercomCode .= ', код домофона ' . $this->getIntercomCode();
        }
        $house = ', д. ' . $this->getHouse();

        $res =
            $this->getStreet() . ' ул.' . $house . $housing . $entrance . $floor . $flat . $intercomCode . ', '
            . $this->getCity();

        return $res;
    }

    /**
     * @return string
     */
    public function getHousing(): string
    {
        return $this->housing ?? '';
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
        return $this->entrance ?? '';
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
    public function getFloor(): string
    {
        return $this->floor ?? '';
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
        return $this->flat ?? '';
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
     * @return string
     */
    public function getIntercomCode(): string
    {
        return $this->intercomCode ?? '';
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
    public function getHouse(): string
    {
        return $this->house ?? '';
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
    public function getStreet(): string
    {
        return $this->street ?? '';
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
    public function getCity(): string
    {
        return $this->city ?? '';
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
    public function getCityLocation(): string
    {
        return $this->cityLocation ?? 0;
    }

    /**
     * @param string $cityLocation
     *
     * @return Address
     */
    public function setCityLocation(string $cityLocation): Address
    {
        $this->cityLocation = $cityLocation;

        return $this;
    }

    public function setCityLocationByEntity()
    {
        /** @var LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');
        try {
            $cities = $locationService->findLocationCity($this->getCity(), '', 1, true);
            $city = reset($cities);
            $this->setCityLocation($city['CODE']);
        } catch (CityNotFoundException $e) {
        }
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
     * @return Address
     */
    public function setDetails(string $details): Address
    {
        $this->details = $details;
        return $this;
    }
}
