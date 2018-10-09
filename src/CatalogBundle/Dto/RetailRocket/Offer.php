<?php

namespace FourPaws\CatalogBundle\Dto\RetailRocket;

use Doctrine\Common\Annotations\Annotation\Required;
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
     * @Serializer\Type("int")
     * @Required()
     *
     * @var int
     */
    protected $groupId;

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
     * @Serializer\XmlList(inline=true, entry="int")
     *
     * @var array
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
    protected $model;

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
     * @return int
     */
    public function getGroupId(): int
    {
        return $this->id;
    }

    /**
     * @param int $groupId
     *
     * @return Offer
     */
    public function setGroupId(int $groupId): Offer
    {
        $this->groupId = $groupId;

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
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @param string $model
     *
     * @return Offer
     */
    public function setModel(string $model): Offer
    {
        $this->model = $model;

        return $this;
    }
}
