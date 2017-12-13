<?php

namespace FourPaws\StoreBundle\Entity;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class Store
{
    const BITRIX_TRUE = 'Y';

    const BITRIX_FALSE = 'N';

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
     * @Serializer\AccessType(type="public_method")
     * @Serializer\Accessor(getter="getRawActive", setter="setRawActive")
     * @Serializer\SerializedName("ACTIVE")
     * @Serializer\Type("string")
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
     * @Serializer\SerializedName("LOCATION_ID")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $userId = 0;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SkipWhenEmpty()
     * @Serializer\SerializedName("LOCATION_ID")
     * @Serializer\Groups(groups={"read"})
     */
    protected $modifiedBy = 0;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("SCHEDULE")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $schedule = '';

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
     * @Serializer\AccessType(type="public_method")
     * @Serializer\Accessor(getter="getRawIssuingCenter", setter="setRawIssuingCenter")
     * @Serializer\SerializedName("ISSUING_CENTER")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $issuingCenter = true;

    /**
     * @var bool
     * @Serializer\AccessType(type="public_method")
     * @Serializer\Accessor(getter="getRawShippingCenter", setter="setRawShippingCenter")
     * @Serializer\SerializedName("SHIPPING_CENTER")
     * @Serializer\Type("string")
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
     * @Serializer\Type("array<int>")
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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

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
    public function getRawActive(): string
    {
        return $this->isActive() ? static::BITRIX_TRUE : static::BITRIX_FALSE;
    }

    /**
     * @param string $active
     *
     * @return Store
     */
    public function setRawActive(string $active)
    {
        return $this->setActive($active === static::BITRIX_TRUE);
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
    public function getDescription(): string
    {
        return $this->description;
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
        return $this->latitude;
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
        return $this->longitude;
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
        return $this->imageId;
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
        return $this->locationId;
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
        return $this->dateModify;
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
        return $this->dateCreate;
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
        return $this->userId;
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
        return $this->modifiedBy;
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
     * @return string
     */
    public function getSchedule(): string
    {
        return $this->schedule;
    }

    /**
     * @param string $schedule
     *
     * @return Store
     */
    public function setSchedule(string $schedule): Store
    {
        $this->schedule = $schedule;

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
        return $this->sort;
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
        return $this->email;
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
     * @return string
     */
    public function getRawIssuingCenter(): string
    {
        return $this->isIssuingCenter() ? static::BITRIX_TRUE : static::BITRIX_FALSE;
    }

    /**
     * @param string $isIssuingCenter
     *
     * @return Store
     */
    public function setRawIssuingCenter(string $isIssuingCenter)
    {
        return $this->setIssuingCenter($isIssuingCenter === static::BITRIX_TRUE);
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
    public function getRawShippingCenter(): string
    {
        return $this->isShippingCenter() ? static::BITRIX_TRUE : static::BITRIX_FALSE;
    }

    /**
     * @param string $isShippingCenter
     *
     * @return Store
     */
    public function setRawShippingCenter(string $isShippingCenter)
    {
        return $this->setShippingCenter($isShippingCenter === static::BITRIX_TRUE);
    }

    /**
     * @return string
     */
    public function getSiteId(): string
    {
        return $this->siteId;
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
        return $this->code;
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
        return $this->isShop;
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
        return $this->location;
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
        return $this->metro;
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
        return $this->yandexShopId;
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
}
