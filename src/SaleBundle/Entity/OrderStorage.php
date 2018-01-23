<?php

namespace FourPaws\SaleBundle\Entity;

use DateTime;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;

class OrderStorage
{
    /**
     * ID пользователя корзины
     *
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("FUSER_ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"auth","delivery","payment"})
     */
    protected $fuserId = 0;

    /**
     * ID типа оплаты
     *
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("PAY_SYSTEM_ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"payment"})
     */
    protected $paymentId = 0;

    /**
     * ID типа доставки
     *
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("DELIVERY_ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"payment","delivery"})
     */
    protected $deliveryId = 0;

    /**
     * Имя
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_NAME")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"auth", "payment","delivery"})
     */
    protected $name = '';

    /**
     * Телефон
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_PHONE")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"auth", "payment","delivery"})
     * @PhoneNumber(defaultRegion="RU",type="mobile")
     */
    protected $phone = '';

    /**
     * Доп. телефон
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_PHONE_ALT")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @PhoneNumber(defaultRegion="RU",type="mobile")
     */
    protected $altPhone = '';

    /**
     * E-mail
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_EMAIL")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\Email(groups={"auth", "payment","delivery"})
     */
    protected $email = '';

    /**
     * Улица
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_STREET")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"payment","delivery"})
     */
    protected $street = '';

    /**
     * Дом
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_HOUSE")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $house = '';

    /**
     * Корпус
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_BUILDING")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $building = '';

    /**
     * Квартира
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_APARTMENT")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $apartment = '';

    /**
     * Подъезд
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_PORCH")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $porch = '';

    /**
     * Этаж
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_FLOOR")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $floor = '';

    /**
     * Имя профиля
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_PROFILE_NAME")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $profileName = '';

    /**
     * Дата доставки
     *
     * @var DateTime
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_DELIVERY_DATE")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $deliveryDate;

    /**
     * Интервал доставки
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_DELIVERY_INTERVAL")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $deliveryInterval = '';

    /**
     * Код места доставки
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_DELIVERY_PLACE_CODE")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $deliveryPlaceCode = '';

    /**
     * Код терминала DPD
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_DPD_TERMINAL_CODE")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $dpdTerminalCode = '';

    /**
     * Способ коммуникации
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_COM_WAY")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"auth", "payment","delivery"})
     */
    protected $communicationWay = '';

    /**
     * Довоз с РЦ для курьера
     *
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("PROPERTY_REGION_COURIER_FROM_DC")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $regionCourierFromDC = false;

    /**
     * Код источника заказа
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_SOURCE_CODE")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $sourceCode = '';

    /**
     * Код партнера
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_PARTNER_CODE")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $partnerCode = '';

    /**
     * Заказ из приложения
     *
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("PROPERTY_FROM_APP")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $fromApp = false;

    /**
     * Наличие одежды
     *
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("PROPERTY_HAS_CLOTHES")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $hasClothes = false;

    /**
     * Выгружен в SAP
     *
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("PROPERTY_IS_EXPORTED")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $isExported = false;

    /**
     * Город
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_CITY")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"payment","delivery"})
     */
    protected $city = '';

    /**
     * Город (местоположение)
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_CITY_CODE")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"payment","delivery"})
     */
    protected $cityCode = '';

    /**
     * @return int
     */
    public function getFuserId(): int
    {
        return $this->fuserId;
    }

    /**
     * @param int $fuserId
     *
     * @return OrderStorage
     */
    public function setFuserId(int $fuserId): OrderStorage
    {
        $this->fuserId = $fuserId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPaymentId(): int
    {
        return $this->paymentId;
    }

    /**
     * @param int $paymentId
     *
     * @return OrderStorage
     */
    public function setPaymentId(int $paymentId): OrderStorage
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryId(): int
    {
        return $this->deliveryId;
    }

    /**
     * @param int $deliveryId
     *
     * @return OrderStorage
     */
    public function setDeliveryId(int $deliveryId): OrderStorage
    {
        $this->deliveryId = $deliveryId;

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
     * @return OrderStorage
     */
    public function setName(string $name): OrderStorage
    {
        $this->name = $name;

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
     * @return OrderStorage
     */
    public function setPhone(string $phone): OrderStorage
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getAltPhone(): string
    {
        return $this->altPhone;
    }

    /**
     * @param string $altPhone
     *
     * @return OrderStorage
     */
    public function setAltPhone(string $altPhone): OrderStorage
    {
        $this->altPhone = $altPhone;

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
     * @return OrderStorage
     */
    public function setEmail(string $email): OrderStorage
    {
        $this->email = $email;

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
     * @return OrderStorage
     */
    public function setStreet(string $street): OrderStorage
    {
        $this->street = $street;

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
     * @return OrderStorage
     */
    public function setHouse(string $house): OrderStorage
    {
        $this->house = $house;

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
     * @return OrderStorage
     */
    public function setBuilding(string $building): OrderStorage
    {
        $this->building = $building;

        return $this;
    }

    /**
     * @return string
     */
    public function getApartment(): string
    {
        return $this->apartment;
    }

    /**
     * @param string $apartment
     *
     * @return OrderStorage
     */
    public function setApartment(string $apartment): OrderStorage
    {
        $this->apartment = $apartment;

        return $this;
    }

    /**
     * @return string
     */
    public function getPorch(): string
    {
        return $this->porch;
    }

    /**
     * @param string $porch
     *
     * @return OrderStorage
     */
    public function setPorch(string $porch): OrderStorage
    {
        $this->porch = $porch;

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
     * @return OrderStorage
     */
    public function setFloor(string $floor): OrderStorage
    {
        $this->floor = $floor;

        return $this;
    }

    /**
     * @return string
     */
    public function getProfileName(): string
    {
        return $this->profileName;
    }

    /**
     * @param string $profileName
     *
     * @return OrderStorage
     */
    public function setProfileName(string $profileName): OrderStorage
    {
        $this->profileName = $profileName;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDeliveryDate(): DateTime
    {
        return $this->deliveryDate;
    }

    /**
     * @param DateTime $deliveryDate
     *
     * @return OrderStorage
     */
    public function setDeliveryDate(DateTime $deliveryDate): OrderStorage
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryInterval(): string
    {
        return $this->deliveryInterval;
    }

    /**
     * @param string $deliveryInterval
     *
     * @return OrderStorage
     */
    public function setDeliveryInterval(string $deliveryInterval): OrderStorage
    {
        $this->deliveryInterval = $deliveryInterval;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryPlaceCode(): string
    {
        return $this->deliveryPlaceCode;
    }

    /**
     * @param string $deliveryPlaceCode
     *
     * @return OrderStorage
     */
    public function setDeliveryPlaceCode(string $deliveryPlaceCode): OrderStorage
    {
        $this->deliveryPlaceCode = $deliveryPlaceCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getDpdTerminalCode(): string
    {
        return $this->dpdTerminalCode;
    }

    /**
     * @param string $dpdTerminalCode
     *
     * @return OrderStorage
     */
    public function setDpdTerminalCode(string $dpdTerminalCode): OrderStorage
    {
        $this->dpdTerminalCode = $dpdTerminalCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCommunicationWay(): string
    {
        return $this->communicationWay;
    }

    /**
     * @param string $communicationWay
     *
     * @return OrderStorage
     */
    public function setCommunicationWay(string $communicationWay): OrderStorage
    {
        $this->communicationWay = $communicationWay;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRegionCourierFromDC(): bool
    {
        return $this->regionCourierFromDC;
    }

    /**
     * @param bool $regionCourierFromDC
     *
     * @return OrderStorage
     */
    public function setRegionCourierFromDC(bool $regionCourierFromDC): OrderStorage
    {
        $this->regionCourierFromDC = $regionCourierFromDC;

        return $this;
    }

    /**
     * @return string
     */
    public function getSourceCode(): string
    {
        return $this->sourceCode;
    }

    /**
     * @param string $sourceCode
     *
     * @return OrderStorage
     */
    public function setSourceCode(string $sourceCode): OrderStorage
    {
        $this->sourceCode = $sourceCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getPartnerCode(): string
    {
        return $this->partnerCode;
    }

    /**
     * @param string $partnerCode
     *
     * @return OrderStorage
     */
    public function setPartnerCode(string $partnerCode): OrderStorage
    {
        $this->partnerCode = $partnerCode;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFromApp(): bool
    {
        return $this->fromApp;
    }

    /**
     * @param bool $fromApp
     *
     * @return OrderStorage
     */
    public function setFromApp(bool $fromApp): OrderStorage
    {
        $this->fromApp = $fromApp;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHasClothes(): bool
    {
        return $this->hasClothes;
    }

    /**
     * @param bool $hasClothes
     *
     * @return OrderStorage
     */
    public function setHasClothes(bool $hasClothes): OrderStorage
    {
        $this->hasClothes = $hasClothes;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExported(): bool
    {
        return $this->isExported;
    }

    /**
     * @param bool $isExported
     *
     * @return OrderStorage
     */
    public function setIsExported(bool $isExported): OrderStorage
    {
        $this->isExported = $isExported;

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
     * @return OrderStorage
     */
    public function setCity(string $city): OrderStorage
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getCityCode(): string
    {
        return $this->cityCode;
    }

    /**
     * @param string $cityCode
     *
     * @return OrderStorage
     */
    public function setCityCode(string $cityCode): OrderStorage
    {
        $this->cityCode = $cityCode;

        return $this;
    }
}
