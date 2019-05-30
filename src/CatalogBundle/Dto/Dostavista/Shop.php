<?php

namespace FourPaws\CatalogBundle\Dto\Dostavista;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Shop
 *
 * @package FourPaws\CatalogBundle\Dto\Dostavista
 */
class Shop
{
    /**
     * Имя магазина
     *
     * @Required()
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * Имя компании
     *
     * @Required()
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $company;

    /**
     * Урл сайта
     *
     * @Required()
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $url;

    /**
     * Валюты
     *
     * @Required()
     * @Serializer\XmlList(inline=false, entry="currency")
     * @Serializer\Type("ArrayCollection<FourPaws\CatalogBundle\Dto\Dostavista\Currency>")
     *
     * @var Currency[]|Collection
     */
    protected $currencies;

    /**
     * Категории
     *
     * @Required()
     * @Serializer\XmlList(inline=false, entry="category")
     * @Serializer\Type("ArrayCollection<FourPaws\CatalogBundle\Dto\Dostavista\Category>")
     *
     * @var Category[]|Collection
     */
    protected $categories;

    /**
     * Магазины
     *
     * @Required()
     * @Serializer\XmlList(inline=false, entry="merchant")
     * @Serializer\Type("ArrayCollection<FourPaws\CatalogBundle\Dto\Dostavista\Merchant>")
     *
     * @var Merchant[]|Collection
     */
    protected $merchants;

    /**
     * Торговые предложения
     *
     * @Serializer\XmlList(inline=false, entry="offer")
     * @Serializer\Type("ArrayCollection<FourPaws\CatalogBundle\Dto\Dostavista\Offer>")
     *
     * @var Offer[]|Collection
     */
    protected $offers;

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
     * @return Shop
     */
    public function setName(string $name): Shop
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * @param string $company
     *
     * @return Shop
     */
    public function setCompany(string $company): Shop
    {
        $this->company = $company;

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
     * @return Shop
     */
    public function setUrl(string $url): Shop
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Collection|Currency[]
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }

    /**
     * @param Collection|Currency[] $currencies
     *
     * @return Shop
     */
    public function setCurrencies($currencies): Shop
    {
        $this->currencies = $currencies;

        return $this;
    }

    /**
     * @return ArrayCollection|Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param ArrayCollection|Category[] $categories
     *
     * @return Shop
     */
    public function setCategories($categories): Shop
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return ArrayCollection|Offer[]
     */
    public function getOffers()
    {
        return $this->offers ?? new ArrayCollection();
    }

    /**
     * @param Collection|Offer[] $offers
     *
     * @return Shop
     */
    public function setOffers($offers): Shop
    {
        $this->offers = $offers;

        return $this;
    }

    /**
     * @return Collection|Merchant[]
     */
    public function getMerchants()
    {
        return $this->merchants;
    }

    /**
     * @param Collection|Merchant[] $merchants
     * @return Shop
     */
    public function setMerchants($merchants): Shop
    {
        $this->merchants = $merchants;

        return $this;
    }
}
