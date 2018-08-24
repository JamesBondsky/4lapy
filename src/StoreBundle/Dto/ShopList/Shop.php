<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Dto\ShopList;

use JMS\Serializer\Annotation as Serializer;

class Shop
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $xmlId;

    /**
     * @Serializer\SerializedName("addr")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $address = '';

    /**
     * @Serializer\SerializedName("adress")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $description = '';

    /**
     * @Serializer\SerializedName("phone")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $phone = '';

    /**
     * @Serializer\SerializedName("schedule")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $schedule = '';

    /**
     * @Serializer\SerializedName("photo")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $photoUrl = '';

    /**
     * @Serializer\SerializedName("metro")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $metro = '';

    /**
     * @Serializer\SerializedName("metroClass")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $metroCssClass = '';

    /**
     * @Serializer\SerializedName("services")
     * @Serializer\Type("array<string>")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string[]
     */
    protected $services;

    /**
     * @Serializer\SerializedName("gps_s")
     * @Serializer\Type("float")
     * @Serializer\SkipWhenEmpty()
     *
     * @var float
     */
    protected $latitude = 0;

    /**
     * @Serializer\SerializedName("gps_n")
     * @Serializer\Type("float")
     * @Serializer\SkipWhenEmpty()
     *
     * @var float
     */
    protected $longitude = 0;

    /**
     * @Serializer\SerializedName("pickup")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $pickupDate;

    /**
     * @Serializer\SerializedName("order")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $availability;

    /**
     * @Serializer\SerializedName("amount")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $availableAmount;

    /**
     * @Serializer\SerializedName("location_type")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $locationType;

    /**
     * @Serializer\SerializedName("active")
     * @Serializer\Type("bool")
     * @Serializer\SkipWhenEmpty()
     *
     * @var bool
     */
    protected $active = false;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Shop
     */
    public function setId(int $id): Shop
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getXmlId(): string
    {
        return $this->xmlId;
    }

    /**
     * @param string $xmlId
     *
     * @return Shop
     */
    public function setXmlId(string $xmlId): Shop
    {
        $this->xmlId = $xmlId;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     *
     * @return Shop
     */
    public function setAddress(string $address): Shop
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Shop
     */
    public function setDescription(string $description): Shop
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return Shop
     */
    public function setPhone(string $phone): Shop
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getSchedule(): string
    {
        return $this->schedule;
    }

    /**
     * @param string $schedule
     *
     * @return Shop
     */
    public function setSchedule(string $schedule): Shop
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhotoUrl(): string
    {
        return $this->photoUrl;
    }

    /**
     * @param string $photoUrl
     *
     * @return Shop
     */
    public function setPhotoUrl(string $photoUrl): Shop
    {
        $this->photoUrl = $photoUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetro(): string
    {
        return $this->metro;
    }

    /**
     * @param string $metro
     *
     * @return Shop
     */
    public function setMetro(string $metro): Shop
    {
        $this->metro = $metro;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetroCssClass(): string
    {
        return $this->metroCssClass;
    }

    /**
     * @param string $metroCssClass
     *
     * @return Shop
     */
    public function setMetroCssClass(string $metroCssClass): Shop
    {
        $this->metroCssClass = $metroCssClass;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * @param string[] $services
     *
     * @return Shop
     */
    public function setServices(array $services): Shop
    {
        $this->services = $services;

        return $this;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     *
     * @return Shop
     */
    public function setLatitude(float $latitude): Shop
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     *
     * @return Shop
     */
    public function setLongitude(float $longitude): Shop
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return string
     */
    public function getPickupDate(): string
    {
        return $this->pickupDate;
    }

    /**
     * @param string $pickupDate
     *
     * @return Shop
     */
    public function setPickupDate(string $pickupDate): Shop
    {
        $this->pickupDate = $pickupDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getAvailability(): string
    {
        return $this->availability;
    }

    /**
     * @param string $availability
     * @return Shop
     */
    public function setAvailability(string $availability): Shop
    {
        $this->availability = $availability;

        return $this;
    }

    /**
     * @return string
     */
    public function getAvailableAmount(): string
    {
        return $this->availableAmount;
    }

    /**
     * @param string $availableAmount
     *
     * @return Shop
     */
    public function setAvailableAmount(string $availableAmount): Shop
    {
        $this->availableAmount = $availableAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocationType(): string
    {
        return $this->locationType;
    }

    /**
     * @param string $locationType
     * @return Shop
     */
    public function setLocationType(string $locationType): Shop
    {
        $this->locationType = $locationType;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return Shop
     */
    public function setActive(bool $active): Shop
    {
        $this->active = $active;

        return $this;
    }
}

