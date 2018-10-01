<?php

namespace FourPaws\SaleBundle\Dto\ShopList;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;

class Shop
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     *
     * @Serializer\SerializedName("id")
     * @Serializer\Type("string")
     */
    protected $xmlId;

    /**
     * @var string
     *
     * @Serializer\SerializedName("name")
     * @Serializer\Type("string")
     */
    protected $name = '';

    /**
     * @var string
     *
     * @Serializer\SerializedName("adress")
     * @Serializer\Type("string")
     */
    protected $address = '';

    /**
     * @var string
     * @Serializer\SerializedName("phone")
     * @Serializer\Type("string")
     */
    protected $phone = '';

    /**
     * @var string
     *
     * @Serializer\SerializedName("schedule")
     * @Serializer\Type("string")
     */
    protected $schedule = '';

    /**
     * @var string
     *
     * @Serializer\SerializedName("metroClass")
     * @Serializer\Type("string")
     */
    protected $metroCssClass = '';

    /**
     * @var float
     *
     * @Serializer\SerializedName("gps_s")
     * @Serializer\Type("float")
     */
    protected $latitude = 0;

    /**
     * @var float
     *
     * @Serializer\SerializedName("gps_n")
     * @Serializer\Type("float")
     */
    protected $longitude = 0;

    /**
     * @var string
     *
     * @Serializer\SerializedName("order")
     * @Serializer\Type("string")
     */
    protected $availability;

    /**
     * @var string
     *
     * @Serializer\SerializedName("location_type")
     * @Serializer\Type("string")
     */
    protected $locationType;

    /**
     * @var ArrayCollection
     *
     * @Serializer\SerializedName("payments")
     * @Serializer\Type("ArrayCollection<int, FourPaws\SaleBundle\Dto\ShopList\Payment>")
     */
    protected $payments;

    /**
     * @var ArrayCollection
     *
     * @Serializer\SerializedName("parts_available")
     * @Serializer\Type("ArrayCollection<FourPaws\SaleBundle\Dto\ShopList\Offer>")
     */
    protected $availableItems;

    /**
     * @var ArrayCollection
     *
     * @Serializer\SerializedName("parts_delayed")
     * @Serializer\Type("ArrayCollection<FourPaws\SaleBundle\Dto\ShopList\Offer>")
     */
    protected $delayedItems;

    /**
     * @var string
     *
     * @Serializer\SerializedName("price")
     * @Serializer\Type("string")
     */
    protected $price;

    /**
     * @var string
     *
     * @Serializer\SerializedName("full_price")
     * @Serializer\Type("string")
     */
    protected $fullPrice;

    /**
     * @var string
     *
     * @Serializer\SerializedName("pickup")
     * @Serializer\Type("string")
     */
    protected $pickupDate;

    /**
     * @var string
     *
     * @Serializer\SerializedName("pickup_full")
     * @Serializer\Type("string")
     */
    protected $fullPickupDate;

    /**
     * @var string
     *
     * @Serializer\SerializedName("pickup_short")
     * @Serializer\Type("string")
     */
    protected $pickupDateShortFormat;

    /**
     * @var string
     *
     * @Serializer\SerializedName("pickup_short_full")
     * @Serializer\Type("string")
     */
    protected $fullPickupDateShortFormat;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Shop
     */
    public function setName(string $name): Shop
    {
        $this->name = $name;

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
    public function getAvailability(): string
    {
        return $this->availability;
    }

    /**
     * @param string $availability
     *
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
    public function getLocationType(): string
    {
        return $this->locationType;
    }

    /**
     * @param string $locationType
     *
     * @return Shop
     */
    public function setLocationType(string $locationType): Shop
    {
        $this->locationType = $locationType;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPayments(): ArrayCollection
    {
        return $this->payments;
    }

    /**
     * @param ArrayCollection $payments
     *
     * @return Shop
     */
    public function setPayments(ArrayCollection $payments): Shop
    {
        $this->payments = $payments;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAvailableItems(): ArrayCollection
    {
        return $this->availableItems;
    }

    /**
     * @param ArrayCollection $availableItems
     *
     * @return Shop
     */
    public function setAvailableItems(ArrayCollection $availableItems): Shop
    {
        $this->availableItems = $availableItems;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getDelayedItems(): ArrayCollection
    {
        return $this->delayedItems;
    }

    /**
     * @param ArrayCollection $delayedItems
     *
     * @return Shop
     */
    public function setDelayedItems(ArrayCollection $delayedItems): Shop
    {
        $this->delayedItems = $delayedItems;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @param string $price
     *
     * @return Shop
     */
    public function setPrice(string $price): Shop
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullPrice(): string
    {
        return $this->fullPrice;
    }

    /**
     * @param string $fullPrice
     *
     * @return Shop
     */
    public function setFullPrice(string $fullPrice): Shop
    {
        $this->fullPrice = $fullPrice;

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
    public function getFullPickupDate(): string
    {
        return $this->fullPickupDate;
    }

    /**
     * @param string $fullPickupDate
     *
     * @return Shop
     */
    public function setFullPickupDate(string $fullPickupDate): Shop
    {
        $this->fullPickupDate = $fullPickupDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getPickupDateShortFormat(): string
    {
        return $this->pickupDateShortFormat;
    }

    /**
     * @param string $pickupDateShortFormat
     *
     * @return Shop
     */
    public function setPickupDateShortFormat(string $pickupDateShortFormat): Shop
    {
        $this->pickupDateShortFormat = $pickupDateShortFormat;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullPickupDateShortFormat(): string
    {
        return $this->fullPickupDateShortFormat;
    }

    /**
     * @param string $fullPickupDateShortFormat
     *
     * @return Shop
     */
    public function setFullPickupDateShortFormat(string $fullPickupDateShortFormat): Shop
    {
        $this->fullPickupDateShortFormat = $fullPickupDateShortFormat;

        return $this;
    }
}

