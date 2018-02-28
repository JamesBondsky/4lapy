<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Store;

use JMS\Serializer\Annotation as Serializer;

class Store
{
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_id")
     */
    protected $cityId;
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
     */
    protected $picture;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("details")
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
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("phone_ext")
     */
    protected $phoneExt;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("url")
     */
    protected $url;
    /**
     * @var StoreService[]
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Store\StoreService>")
     * @Serializer\SerializedName("service")
     */
    protected $service;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("availability_status")
     */
    protected $availabilityStatus;

    /**
     * @return string
     */
    public function getCityId(): string
    {
        return $this->cityId;
    }

    /**
     * @param string $cityId
     *
     * @return Store
     */
    public function setCityId(string $cityId): Store
    {
        $this->cityId = $cityId;
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
        $this->picture = $picture;
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
     * @return string
     */
    public function getPhoneExt(): string
    {
        return $this->phoneExt;
    }

    /**
     * @param string $phoneExt
     *
     * @return Store
     */
    public function setPhoneExt(string $phoneExt): Store
    {
        $this->phoneExt = $phoneExt;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Store
     */
    public function setUrl(string $url): Store
    {
        $this->url = $url;
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
     * @return string
     */
    public function getAvailabilityStatus(): string
    {
        return $this->availabilityStatus;
    }

    /**
     * @param string $availabilityStatus
     *
     * @return Store
     */
    public function setAvailabilityStatus(string $availabilityStatus): Store
    {
        $this->availabilityStatus = $availabilityStatus;
        return $this;
    }
}