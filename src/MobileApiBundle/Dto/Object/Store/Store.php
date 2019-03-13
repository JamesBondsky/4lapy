<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Store;

use Bitrix\Main\SystemException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\MobileApiBundle\Collection\BasketProductCollection;
use JMS\Serializer\Annotation as Serializer;

class Store
{
    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Type("string")
     * @var string
     */
    protected $code;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     */
    protected $title;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("picture")
     * @Serializer\Groups({"withProductInfo", "withShopDetails"})
     */
    protected $picture;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("details")
     * @Serializer\Groups({"withProductInfo", "withShopDetails"})
     */
    protected $details;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("lat")
     */
    protected $latitude;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("lon")
     */
    protected $longitude;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("metro_name")
     */
    protected $metroName;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("metro_color")
     */
    protected $metroColor;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("worktime")
     */
    protected $workTime;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("address")
     */
    protected $address;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("phone")
     */
    protected $phone;
    /**
     * @var StoreService[]
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Store\StoreService>")
     * @Serializer\SerializedName("service")
     * @Serializer\Groups({"withProductInfo", "withShopDetails"})
     */
    protected $service;

    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("isByRequest")
     * @Serializer\Groups({"withProductInfo"})
     */
    protected $isByRequest;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("productQuantityString")
     * @Serializer\Groups({"withProductInfo"})
     */
    protected $productQuantityString;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("pickupDate")
     * @Serializer\Groups({"withProductInfo", "withPickupInfo"})
     */
    protected $pickupDate;

    /**
     * @var string
     *
     * @Serializer\SerializedName("pickupAllGoodsShortDate")
     * @Serializer\Type("string")
     * @Serializer\Groups({"withPickupInfo"})
     */
    protected $pickupAllGoodsShortDate;

    /**
     * @var string
     *
     * @Serializer\SerializedName("pickupAllGoodsFullDate")
     * @Serializer\Type("string")
     * @Serializer\Groups({"withPickupInfo"})
     */
    protected $pickupAllGoodsFullDate;

    /**
     * @var string
     *
     * @Serializer\SerializedName("pickupFewGoodsShortDate")
     * @Serializer\Type("string")
     * @Serializer\Groups({"withPickupInfo"})
     */
    protected $pickupFewGoodsShortDate;

    /**
     * @var string
     *
     * @Serializer\SerializedName("pickupFewGoodsFullDate")
     * @Serializer\Type("string")
     * @Serializer\Groups({"withPickupInfo"})
     */
    protected $pickupFewGoodsFullDate;

    /**
     * @var string
     *
     * @Serializer\SerializedName("pickupAllGoodsTitle")
     * @Serializer\Type("string")
     * @Serializer\Groups({"withPickupInfo"})
     */
    protected $pickupAllGoodsTitle;

    /**
     * @var string
     *
     * @Serializer\SerializedName("pickupAvailableGoodsTitle")
     * @Serializer\Type("string")
     * @Serializer\Groups({"withPickupInfo"})
     */
    protected $pickupAvailableGoodsTitle;

    /**
     * @var string
     *
     * @Serializer\SerializedName("pickupDelayedGoodsTitle")
     * @Serializer\Type("string")
     * @Serializer\Groups({"withPickupInfo"})
     */
    protected $pickupDelayedGoodsTitle;

    /**
     * @var ArrayCollection
     *
     * @Serializer\SerializedName("availableGoods")
     * @Serializer\Type("FourPaws\MobileApiBundle\Collection\BasketProductCollection")
     * @Serializer\Groups({"withPickupInfo"})
     */
    protected $availableGoods;

    /**
     * @var ArrayCollection
     *
     * @Serializer\SerializedName("delayedGoods")
     * @Serializer\Type("FourPaws\MobileApiBundle\Collection\BasketProductCollection")
     * @Serializer\Groups({"withPickupInfo"})
     */
    protected $delayedGoods;

    /**
     * @var string
     *
     * @Serializer\SerializedName("availability")
     * @Serializer\Type("string")
     * @Serializer\Groups({"withPickupInfo"})
     */
    protected $availability;

    /**
     * @param string $code
     * @return Store
     */
    public function setCode(string $code): Store {
        $this->code = $code;
        return $this;
    }

    /**
     * @return int
     */
    public function getCode(): int {
        return $this->code;
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
     *
     * @return Store
     */
    public function setTitle(string $title): Store
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getPicture(): string
    {
        return $this->picture;
    }

    /**
     * @param string $picture
     *
     * @return Store
     */
    public function setPicture(string $picture): Store
    {
        $this->picture = (string) new FullHrefDecorator($picture);
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
     *
     * @return Store
     */
    public function setDetails(string $details): Store
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @return string
     */
    public function getLatitude(): string
    {
        return $this->latitude;
    }

    /**
     * @param string $latitude
     *
     * @return Store
     */
    public function setLatitude(string $latitude): Store
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return string
     */
    public function getLongitude(): string
    {
        return $this->longitude;
    }

    /**
     * @param string $longitude
     *
     * @return Store
     */
    public function setLongitude(string $longitude): Store
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return string
     */
    public function getMetroName(): string
    {
        return $this->metroName;
    }

    /**
     * @param string $metroName
     *
     * @return Store
     */
    public function setMetroName(string $metroName): Store
    {
        $this->metroName = $metroName;
        return $this;
    }

    /**
     * @return string
     */
    public function getMetroColor(): string
    {
        return $this->metroColor;
    }

    /**
     * @param string $metroColor
     *
     * @return Store
     */
    public function setMetroColor(string $metroColor): Store
    {
        $this->metroColor = $metroColor;
        return $this;
    }

    /**
     * @return string
     */
    public function getWorkTime(): string
    {
        return $this->workTime;
    }

    /**
     * @param string $workTime
     *
     * @return Store
     */
    public function setWorkTime(string $workTime): Store
    {
        $this->workTime = $workTime;
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
     * @return Store
     */
    public function setAddress(string $address): Store
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
     * @return Store
     */
    public function setPhone(string $phone): Store
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return StoreService[]
     */
    public function getService(): array
    {
        return $this->service;
    }

    /**
     * @param StoreService[] $service
     *
     * @return Store
     */
    public function setService(array $service): Store
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsByRequest(): bool
    {
        return $this->isByRequest;
    }

    /**
     * @param bool $isByRequest
     * @return Store
     */
    public function setIsByRequest(bool $isByRequest): Store
    {
        $this->isByRequest = $isByRequest;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductQuantityString(): string
    {
        return $this->productQuantityString;
    }

    /**
     * @param string $productQuantityString
     *
     * @return Store
     */
    public function setProductQuantityString(string $productQuantityString): Store
    {
        $this->productQuantityString = $productQuantityString;
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
     * @return Store
     */
    public function setPickupDate(string $pickupDate): Store
    {
        $this->pickupDate = $pickupDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getPickupAllGoodsShortDate(): string
    {
        return $this->pickupAllGoodsShortDate;
    }

    /**
     * @param string $pickupAllGoodsShortDate
     *
     * @return Store
     */
    public function setPickupAllGoodsShortDate(string $pickupAllGoodsShortDate): Store
    {
        $this->pickupAllGoodsShortDate = $pickupAllGoodsShortDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getPickupAllGoodsFullDate(): string
    {
        return $this->pickupAllGoodsFullDate;
    }

    /**
     * @param string $pickupAllGoodsFullDate
     *
     * @return Store
     */
    public function setPickupAllGoodsFullDate(string $pickupAllGoodsFullDate): Store
    {
        $this->pickupAllGoodsFullDate = $pickupAllGoodsFullDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getPickupFewGoodsShortDate(): string
    {
        return $this->pickupFewGoodsShortDate;
    }

    /**
     * @param string $pickupFewGoodsShortDate
     *
     * @return Store
     */
    public function setPickupFewGoodsShortDate(string $pickupFewGoodsShortDate): Store
    {
        $this->pickupFewGoodsShortDate = $pickupFewGoodsShortDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getPickupFewGoodsFullDate(): string
    {
        return $this->pickupFewGoodsFullDate;
    }

    /**
     * @param string $pickupFewGoodsFullDate
     *
     * @return Store
     */
    public function setPickupFewGoodsFullDate(string $pickupFewGoodsFullDate): Store
    {
        $this->pickupFewGoodsFullDate = $pickupFewGoodsFullDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getPickupAllGoodsTitle(): string
    {
        return $this->pickupAllGoodsTitle;
    }

    /**
     * @param string $pickupAllGoodsTitle
     *
     * @return Store
     */
    public function setPickupAllGoodsTitle(string $pickupAllGoodsTitle): Store
    {
        $this->pickupAllGoodsTitle = $pickupAllGoodsTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getPickupAvailableGoodsTitle(): string
    {
        return $this->pickupAvailableGoodsTitle;
    }

    /**
     * @param string $pickupAvailableGoodsTitle
     *
     * @return Store
     */
    public function setPickupAvailableGoodsTitle(string $pickupAvailableGoodsTitle): Store
    {
        $this->pickupAvailableGoodsTitle = $pickupAvailableGoodsTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getPickupDelayedGoodsTitle(): string
    {
        return $this->pickupDelayedGoodsTitle;
    }

    /**
     * @param string $pickupDelayedGoodsTitle
     *
     * @return Store
     */
    public function setPickupDelayedGoodsTitle(string $pickupDelayedGoodsTitle): Store
    {
        $this->pickupDelayedGoodsTitle = $pickupDelayedGoodsTitle;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAvailableGoods(): ArrayCollection
    {
        return $this->availableGoods;
    }

    /**
     * @param BasketProductCollection $availableGoods
     * @return Store
     */
    public function setAvailableGoods(BasketProductCollection $availableGoods): Store
    {
        $this->availableGoods = $availableGoods;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getDelayedGoods(): ArrayCollection
    {
        return $this->delayedGoods;
    }

    /**
     * @param BasketProductCollection $delayedGoods
     *
     * @return Store
     */
    public function setDelayedGoods(BasketProductCollection $delayedGoods): Store
    {
        $this->delayedGoods = $delayedGoods;
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
     * @return Store
     */
    public function setAvailability(string $availability): Store
    {
        $this->availability = $availability;

        return $this;
    }
}