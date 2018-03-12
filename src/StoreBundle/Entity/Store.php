<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Entity;

use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\BitrixOrm\Model\Image;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class Store extends Base
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"read","update","delete"})
     * @Assert\Blank(groups={"create"})
     */
    protected $id = 0;

    /**
     * @var bool
     * @Serializer\SerializedName("ACTIVE")
     * @Serializer\Type("bitrix_bool")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $active = true;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("TITLE")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $title = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ADDRESS")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $address = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("DESCRIPTION")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $description = '';

    /**
     * @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("GPS_N")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $latitude = 0;

    /**
     * @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("GPS_S")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $longitude = 0;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SkipWhenEmpty()
     * @Serializer\SerializedName("IMAGE_ID")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $imageId = 0;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SkipWhenEmpty()
     * @Serializer\SerializedName("LOCATION_ID")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $locationId = 0;

    /**
     * @var string
     * @Serializer\Type("DateTime<'d.m.Y H:i:s'>")
     * @Serializer\SkipWhenEmpty()
     * @Serializer\SerializedName("DATE_MODIFY")
     * @Serializer\Groups(groups={"read"})
     */
    protected $dateModify = '';

    /**
     * @var string
     * @Serializer\Type("DateTime<'d.m.Y H:i:s'>")
     * @Serializer\SkipWhenEmpty()
     * @Serializer\SerializedName("DATE_CREATE")
     * @Serializer\Groups(groups={"read"})
     */
    protected $dateCreate = '';

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SkipWhenEmpty()
     * @Serializer\SerializedName("USER_ID")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $userId = 0;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SkipWhenEmpty()
     * @Serializer\SerializedName("MODIFIED_BY")
     * @Serializer\Groups(groups={"read"})
     */
    protected $modifiedBy = 0;

    /**
     * @var Schedule
     * @Serializer\Type("store_schedule")
     * @Serializer\SerializedName("SCHEDULE")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $schedule = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PHONE")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $phone = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("XML_ID")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $xmlId = '';

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("SORT")
     * @Serializer\Groups(groups={"read"})
     */
    protected $sort = 500;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("EMAIL")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $email = '';

    /**
     * @var bool
     * @Serializer\SerializedName("ISSUING_CENTER")
     * @Serializer\Type("bitrix_bool")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $issuingCenter = true;

    /**
     * @var bool
     * @Serializer\SerializedName("SHIPPING_CENTER")
     * @Serializer\Type("bitrix_bool")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $shippingCenter = true;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     * @Serializer\SerializedName("SITE_ID")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $siteId = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     * @Serializer\SerializedName("CODE")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $code = '';

    /**
     * @var bool
     * @Serializer\SerializedName("UF_IS_SHOP")
     * @Serializer\Type("bool")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $isShop = false;

    /**
     * @var string
     * @Serializer\SerializedName("UF_LOCATION")
     * @Serializer\SkipWhenEmpty()
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $location = '';

    /**
     * @var int
     * @Serializer\SerializedName("UF_METRO")
     * @Serializer\SkipWhenEmpty()
     * @Serializer\Type("int")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $metro = 0;

    /**
     * @var array
     * @Serializer\SerializedName("UF_SERVICES")
     * @Serializer\SkipWhenEmpty()
     * @Serializer\Type("array_or_false<int>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $services = [];

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     * @Serializer\SerializedName("UF_YANDEX_SHOP_ID")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $yandexShopId = '';

    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("UF_IS_BASE_SHOP")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $isBase = false;

    /**
     * Срок поставки
     *
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_DELIVERY_TIME")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $deliveryTime = 1;

    /**
     * @var array
     * @Serializer\Type("array_or_false")
     * @Serializer\SerializedName("UF_SHIPMENT_TILL_11")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $shipmentTill11;

    /**
     * @var array
     * @Serializer\Type("array_or_false")
     * @Serializer\SerializedName("UF_SHIPMENT_TILL_13")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $shipmentTill13;

    /**
     * @var array
     * @Serializer\Type("array_or_false")
     * @Serializer\SerializedName("UF_SHIPMENT_TILL_18")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $shipmentTill18;

    /**
     * @var string
     */
    protected $scheduleString;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id ?? 0;
    }/** @noinspection SenselessMethodDuplicationInspection */

    /**
     * @param int $id
     *
     * @return Store
     */
    public function setId(int $id): Store
    {
        $this->id = $id;

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
     *
     * @return Store
     */
    public function setActive(bool $active): Store
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title ?? '';
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
    public function getAddress(): string
    {
        return $this->address ?? '';
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
    public function getDescription(): string
    {
        return $this->description ?? '';
    }

    /**
     * @param string $description
     *
     * @return Store
     */
    public function setDescription(string $description): Store
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude ?? 0;
    }

    /**
     * @param float $latitude
     *
     * @return Store
     */
    public function setLatitude(float $latitude): Store
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude ?? 0;
    }

    /**
     * @param float $longitude
     *
     * @return Store
     */
    public function setLongitude(float $longitude): Store
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return int
     */
    public function getImageId(): int
    {
        return $this->imageId ?? 0;
    }

    /**
     * @return string
     */
    public function getSrcImage(): string
    {
        if ($this->getImageId() > 0) {
            try {
                $image = Image::createFromPrimary($this->getImageId());
                return $image->getSrc();
            } catch (FileNotFoundException $e) {
            }
        }
        return '';
    }

    /**
     * @param int $imageId
     *
     * @return Store
     */
    public function setImageId(int $imageId): Store
    {
        $this->imageId = $imageId;

        return $this;
    }

    /**
     * @return int
     */
    public function getLocationId(): int
    {
        return $this->locationId ?? 0;
    }

    /**
     * @param int $locationId
     *
     * @return Store
     */
    public function setLocationId(int $locationId): Store
    {
        $this->locationId = $locationId;

        return $this;
    }

    /**
     * @return int
     */
    public function getDateModify(): int
    {
        return $this->dateModify ?? 0;
    }

    /**
     * @param string $dateModify
     *
     * @return Store
     */
    public function setDateModify(string $dateModify): Store
    {
        $this->dateModify = $dateModify;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateCreate(): string
    {
        return $this->dateCreate ?? '';
    }

    /**
     * @param string $dateCreate
     *
     * @return Store
     */
    public function setDateCreate(string $dateCreate): Store
    {
        $this->dateCreate = $dateCreate;

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
     * @return Store
     */
    public function setUserId(int $userId): Store
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getModifiedBy(): int
    {
        return $this->modifiedBy ?? 0;
    }

    /**
     * @param int $modifiedBy
     *
     * @return Store
     */
    public function setModifiedBy(int $modifiedBy): Store
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    /**
     * @return Schedule
     */
    public function getSchedule(): Schedule
    {
        return $this->schedule;
    }

    /**
     * @param Schedule $schedule
     *
     * @return Store
     */
    public function setSchedule(Schedule $schedule): Store
    {
        $this->schedule = $schedule;
        $this->scheduleString = (string)$schedule;

        return $this;
    }

    /**
     * @return string
     */
    public function getXmlId(): string
    {
        return $this->xmlId ?? '';
    }

    /**
     * @param string $xmlId
     *
     * @return Store
     */
    public function setXmlId(string $xmlId): Store
    {
        $this->xmlId = $xmlId;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort ?? 0;
    }

    /**
     * @param int $sort
     *
     * @return Store
     */
    public function setSort(int $sort): Store
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email ?? '';
    }

    /**
     * @param string $email
     *
     * @return Store
     */
    public function setEmail(string $email): Store
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIssuingCenter(): bool
    {
        return $this->issuingCenter;
    }

    /**
     * @param bool $issuingCenter
     *
     * @return Store
     */
    public function setIssuingCenter(bool $issuingCenter): Store
    {
        $this->issuingCenter = $issuingCenter;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShippingCenter(): bool
    {
        return $this->shippingCenter;
    }

    /**
     * @param bool $shippingCenter
     *
     * @return Store
     */
    public function setShippingCenter(bool $shippingCenter): Store
    {
        $this->shippingCenter = $shippingCenter;

        return $this;
    }

    /**
     * @return string
     */
    public function getSiteId(): string
    {
        return $this->siteId ?? '';
    }

    /**
     * @param string $siteId
     *
     * @return Store
     */
    public function setSiteId(string $siteId): Store
    {
        $this->siteId = $siteId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code ?? '';
    }

    /**
     * @param string $code
     *
     * @return Store
     */
    public function setCode(string $code): Store
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShop(): bool
    {
        return (bool)$this->isShop;
    }

    /**
     * @param bool $isShop
     *
     * @return Store
     */
    public function setIsShop(bool $isShop): Store
    {
        $this->isShop = $isShop;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location ?? '';
    }

    /**
     * @param string $location
     *
     * @return Store
     */
    public function setLocation(string $location): Store
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return int
     */
    public function getMetro(): int
    {
        return $this->metro ?? 0;
    }

    /**
     * @param int $metro
     *
     * @return Store
     */
    public function setMetro(int $metro): Store
    {
        $this->metro = $metro;

        return $this;
    }

    /**
     * @return array
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * @param array $services
     *
     * @return Store
     */
    public function setServices(array $services): Store
    {
        $this->services = $services;

        return $this;
    }

    /**
     * @return string
     */
    public function getYandexShopId(): string
    {
        return $this->yandexShopId ?? '';
    }

    /**
     * @param string $yandexShopId
     *
     * @return Store
     */
    public function setYandexShopId(string $yandexShopId): Store
    {
        $this->yandexShopId = $yandexShopId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBase(): bool
    {
        return (bool)$this->isBase;
    }

    /**
     * @param bool $isBase
     *
     * @return Store
     */
    public function setIsBase(bool $isBase): Store
    {
        $this->isBase = $isBase;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone ?? '';
    }

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone(string $phone): Store
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryTime(): int
    {
        return $this->deliveryTime ?? 0;
    }

    /**
     * @param int $deliveryTime
     * @return Store
     */
    public function setDeliveryTime(int $deliveryTime): Store
    {
        $this->deliveryTime = $deliveryTime;
        return $this;
    }

    /**
     * @return array
     */
    public function getShipmentTill11(): array
    {
        return $this->shipmentTill11 ?? [];
    }

    /**
     * @param array $shipmentTill11
     * @return Store
     */
    public function setShipmentTill11(array $shipmentTill11): Store
    {
        $this->shipmentTill11 = $shipmentTill11;
        return $this;
    }

    /**
     * @return array
     */
    public function getShipmentTill13(): array
    {
        return $this->shipmentTill13 ?? [];
    }

    /**
     * @param array $shipmentTill13
     * @return Store
     */
    public function setShipmentTill13(array $shipmentTill13): Store
    {
        $this->shipmentTill13 = $shipmentTill13;
        return $this;
    }

    /**
     * @return array
     */
    public function getShipmentTill18(): array
    {
        return $this->shipmentTill18 ?? [];
    }

    /**
     * @param array $shipmentTill18
     * @return Store
     */
    public function setShipmentTill18(array $shipmentTill18): Store
    {
        $this->shipmentTill18 = $shipmentTill18;
        return $this;
    }

    /**
     * @todo убрать этот метод. Сейчас нужен для работы с терминалами DPD, где график работы в произвольном формате
     *
     * @return string
     */
    public function getScheduleString(): string
    {
        if (!$this->scheduleString) {
            $this->scheduleString = (string)$this->schedule;
        }

        return $this->scheduleString;
    }

    /**
     * @param string $scheduleString
     * @return Store
     */
    public function setScheduleString(string $scheduleString): Store
    {
        $this->scheduleString = $scheduleString;
        return $this;
    }
}
