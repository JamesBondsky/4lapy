<?php

namespace FourPaws\CatalogBundle\Dto\GoogleMerchant;

use Doctrine\Common\Annotations\Annotation\Required;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Item
 *
 * @package FourPaws\CatalogBundle\Dto\GoogleMerchant
 *
 * @Serializer\XmlRoot("offer")
 */
class Item
{
    /**
     * @Serializer\XmlElement(cdata=false, namespace="http://base.google.com/ns/1.0")
     * @Serializer\Type("int")
     * @Required()
     *
     * @var int
     */
    protected $id;

    /**
     * @Serializer\XmlElement(cdata=false, namespace="http://base.google.com/ns/1.0")
     * @Serializer\Type("int")
     * @Required()
     *
     * @var int
     */
    protected $groupId;

    /**
     * @Serializer\XmlElement(cdata=false, namespace="http://base.google.com/ns/1.0")
     * @Serializer\Type("string")
     * @Required()
     *
     * @var string
     */
    protected $availability = 'in' . ' ' . 'sto' . 'ck';

    /**
     * @Serializer\XmlElement(cdata=false, namespace="http://base.google.com/ns/1.0")
     * @Serializer\Type("string")
     * @Required()
     *
     * @var string
     */
    protected $condition = 'n' . 'e' . 'w';

    /**
     * @Serializer\XmlElement(cdata=false, namespace="http://base.google.com/ns/1.0")
     * @Serializer\Type("string")
     * @Required()
     *
     * @var string
     */
    protected $adult = 'n' . 'o';

    /**
     * @Serializer\XmlElement(cdata=false, namespace="http://base.google.com/ns/1.0")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $link;

    /**
     * @Serializer\XmlElement(cdata=false, namespace="http://base.google.com/ns/1.0")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $price;

    /**
     * @Serializer\XmlElement(cdata=false, namespace="http://base.google.com/ns/1.0")
     * @Serializer\SerializedName("google_product_category")
     *
     * @var string
     */
    protected $categoryId;

    /**
     * @Serializer\XmlElement(cdata=false, namespace="http://base.google.com/ns/1.0")
     * @Serializer\Type("string")
     * @Serializer\SerializedName("image_link")
     *
     * @var string
     */
    protected $picture;

    /**
     * @Serializer\XmlElement(cdata=false, namespace="http://base.google.com/ns/1.0")
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     *
     * @var string
     */
    protected $name;

    /**
     * @Serializer\XmlElement(cdata=false, namespace="http://base.google.com/ns/1.0")
     * @Serializer\Type("string")
     * @Serializer\SerializedName("brand")
     *
     * @var string
     */
    protected $vendor;

    /**
     * @Serializer\XmlElement(cdata=true, namespace="http://base.google.com/ns/1.0")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $description;

    /**
     * @Serializer\XmlElement(cdata=false, namespace="http://base.google.com/ns/1.0")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $gtin;

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
     * @return Item
     */
    public function setId(int $id): Item
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getGroupId(): int
    {
        return $this->id;
    }

    /**
     * @param int $groupId
     *
     * @return Item
     */
    public function setGroupId(int $groupId): Item
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     *
     * @return Item
     */
    public function setLink(string $link): Item
    {
        $this->link = $link;

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
     * @return Item
     */
    public function setPrice(string $price): Item
    {
        $this->price = $price;

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
     * @return Item
     */
    public function setPicture(string $picture): Item
    {
        $this->picture = $picture;

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
     * @return Item
     */
    public function setName(string $name): Item
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
     * @return Item
     */
    public function setVendor(string $vendor): Item
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
     * @return Item
     */
    public function setDescription(string $description): Item
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getGtin(): string
    {
        return $this->gtin;
    }

    /**
     * @param string $gtin
     *
     * @return Item
     */
    public function setGtin(string $gtin): Item
    {
        $this->gtin = $gtin;

        return $this;
    }
}
