<?php

namespace FourPaws\CatalogBundle\Dto\Yandex;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Offer
 *
 * @package FourPaws\CatalogBundle\Dto\Yandex
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
     * @Serializer\Type("ArrayCollection<FourPaws\CatalogBundle\Dto\Yandex\DeliveryOption>")
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
}
