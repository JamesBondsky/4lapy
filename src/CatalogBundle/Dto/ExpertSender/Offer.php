<?php

namespace FourPaws\CatalogBundle\Dto\ExpertSender;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Offer
 *
 * @package FourPaws\CatalogBundle\Dto\ExpertSender
 *
 * @Serializer\XmlRoot("offer")
 */
class Offer
{
    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Required()
     *
     * @var int
     */
    protected $id;

    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("boolean")
     * @Required()
     *
     * @var boolean
     */
    protected $available = true;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $url;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $oldprice;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $price;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $cpa;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("boolean")
     *
     * @var boolean
     */
    protected $delivery;

    /**
     * Варианты доставки
     *
     * @Serializer\SerializedName("delivery-options")
     * @Serializer\XmlList(inline=false, entry="option")
     * @Serializer\Type("ArrayCollection<FourPaws\CatalogBundle\Dto\ExpertSender\DeliveryOption>")
     *
     * @var DeliveryOption[]|Collection
     */
    protected $deliveryOptions;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $currencyId;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $categoryId;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $picture;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("boolean")
     *
     * @var boolean
     */
    protected $store;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("boolean")
     *
     * @var boolean
     */
    protected $pickup;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $vendor;

    /**
     * @Serializer\XmlElement(cdata=true)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $description;

    /**
     * @Serializer\SkipWhenEmpty()
     * @Serializer\SerializedName("sales_notes")
     * @Serializer\XmlElement(cdata=true)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $salesNotes;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $vendorCode;

    /**
     * @Serializer\SerializedName("manufacturer_warranty")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("boolean")
     *
     * @var boolean
     */
    protected $manufacturerWarranty;

    /**
     * @Serializer\SerializedName("country_of_origin")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $countryOfOrigin;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $barcode;

    /**
     * Параметры оффера
     *
     * @Serializer\SerializedName("param")
     * @Serializer\XmlList(inline=true, entry="param")
     * @Serializer\Type("ArrayCollection<FourPaws\CatalogBundle\Dto\ExpertSender\Param>")
     *
     * @var Param[]|Collection
     */
    protected $param;

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
     * @return Offer
     */
    public function setId(int $id): Offer
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @param bool $available
     *
     * @return Offer
     */
    public function setAvailable(bool $available): Offer
    {
        $this->available = $available;

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
     * @return Offer
     */
    public function setUrl(string $url): Offer
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return Offer
     */
    public function setPrice(float $price): Offer
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getOldPrice(): float
    {
        return $this->oldprice;
    }

    /**
     * @param float $price
     *
     * @return Offer
     */
    public function setOldPrice(float $price): Offer
    {
        $this->oldprice = $price;

        return $this;
    }

    /**
     * @return int
     */
    public function getCpa(): int
    {
        return $this->cpa;
    }

    /**
     * @param int $cpa
     *
     * @return Offer
     */
    public function setCpa(int $cpa): Offer
    {
        $this->cpa = $cpa;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDelivery(): bool
    {
        return $this->delivery;
    }

    /**
     * @param bool $delivery
     *
     * @return Offer
     */
    public function setDelivery(bool $delivery): Offer
    {
        $this->delivery = $delivery;

        return $this;
    }

    /**
     * @return Collection|DeliveryOption[]
     */
    public function getDeliveryOptions()
    {
        return $this->deliveryOptions;
    }

    /**
     * @param Collection|DeliveryOption[] $deliveryOptions
     *
     * @return Offer
     */
    public function setDeliveryOptions($deliveryOptions): Offer
    {
        $this->deliveryOptions = $deliveryOptions;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }

    /**
     * @param string $currencyId
     *
     * @return Offer
     */
    public function setCurrencyId(string $currencyId): Offer
    {
        $this->currencyId = $currencyId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * @param int $categoryId
     *
     * @return Offer
     */
    public function setCategoryId(int $categoryId): Offer
    {
        $this->categoryId = $categoryId;

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
     * @return Offer
     */
    public function setPicture(string $picture): Offer
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStore(): bool
    {
        return $this->store;
    }

    /**
     * @param bool $store
     *
     * @return Offer
     */
    public function setStore(bool $store): Offer
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPickup(): bool
    {
        return $this->pickup;
    }

    /**
     * @param bool $pickup
     *
     * @return Offer
     */
    public function setPickup(bool $pickup): Offer
    {
        $this->pickup = $pickup;

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
     * @return Offer
     */
    public function setName(string $name): Offer
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getVendor(): string
    {
        return $this->vendor;
    }

    /**
     * @param string $vendor
     *
     * @return Offer
     */
    public function setVendor(string $vendor): Offer
    {
        $this->vendor = $vendor;

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
     * @return Offer
     */
    public function setDescription(string $description): Offer
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getVendorCode(): string
    {
        return $this->vendorCode;
    }

    /**
     * @param string $vendorCode
     *
     * @return Offer
     */
    public function setVendorCode(string $vendorCode): Offer
    {
        $this->vendorCode = $vendorCode;

        return $this;
    }

    /**
     * @return bool
     */
    public function isManufacturerWarranty(): bool
    {
        return $this->manufacturerWarranty;
    }

    /**
     * @param bool $manufacturerWarranty
     *
     * @return Offer
     */
    public function setManufacturerWarranty(bool $manufacturerWarranty): Offer
    {
        $this->manufacturerWarranty = $manufacturerWarranty;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryOfOrigin(): string
    {
        return $this->countryOfOrigin;
    }

    /**
     * @param string $countryOfOrigin
     *
     * @return Offer
     */
    public function setCountryOfOrigin(string $countryOfOrigin): Offer
    {
        $this->countryOfOrigin = $countryOfOrigin;

        return $this;
    }

    /**
     * @return string
     */
    public function getBarcode(): string
    {
        return $this->barcode;
    }

    /**
     * @param string $barcode
     *
     * @return Offer
     */
    public function setBarcode(string $barcode): Offer
    {
        $this->barcode = $barcode;

        return $this;
    }

    /**
     * @return Collection|Param[]
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * @param Collection|Param[] $param
     *
     * @return Offer
     */
    public function setParam($param): Offer
    {
        $this->param = $param;

        return $this;
    }

    /**
     * @return string
     */
    public function getSalesNotes(): string
    {
        return $this->salesNotes;
    }

    /**
     * @param string $salesNotes
     *
     * @return Offer
     */
    public function setSalesNotes(string $salesNotes): Offer
    {
        $this->salesNotes = $salesNotes;

        return $this;
    }
}
