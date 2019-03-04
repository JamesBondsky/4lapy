<?php

namespace FourPaws\CatalogBundle\Dto\Dostavista;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Offer
 *
 * @package FourPaws\CatalogBundle\Dto\Dostavista
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
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * @Serializer\XmlElement(cdata=true)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $description;

    /**
     * Остатки в магазинах
     *
     * @Required()
     * @Serializer\XmlList(inline=false, entry="residue")
     * @Serializer\Type("ArrayCollection<FourPaws\CatalogBundle\Dto\Dostavista\Residue>")
     *
     * @var Residue[]|Collection
     */
    protected $residues;

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
     * @return Collection|Residue[]
     */
    public function getResidues()
    {
        return $this->residues;
    }

    /**
     * @param Collection|Residue[] $residues
     * @return Offer
     */
    public function setResidues($residues): Offer
    {
        $this->residues = $residues;

        return $this;
    }
}
