<?php

namespace FourPaws\CatalogBundle\Dto\Yandex;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Shop
 *
 * @package FourPaws\CatalogBundle\Dto\Yandex
 */
class Shop
{
    /**
     * Имя магазина
     *
     * @Required()
     * @Serializer\XmlElement()
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * Имя компании
     *
     * @Required()
     * @Serializer\XmlElement()
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $company;

    /**
     * Урл сайта
     *
     * @Required()
     * @Serializer\XmlElement()
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
     * @Serializer\Type("ArrayCollection<FourPaws\CatalogBundle\Dto\Yandex\Currency>")
     *
     * @var Currency[]|Collection
     */
    protected $currencies;

    /**
     * Категории
     *
     * @Required()
     * @Serializer\XmlList(inline=false, entry="category")
     * @Serializer\Type("ArrayCollection<FourPaws\CatalogBundle\Dto\Yandex\Category>")
     *
     * @var Category[]|Collection
     */
    protected $categories;

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
     * Торговые предложения
     *
     * @Serializer\XmlList(inline=false, entry="offer")
     * @Serializer\Type("ArrayCollection<FourPaws\CatalogBundle\Dto\Yandex\Offer>")
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
     * @return Collection|Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param Collection|Category[] $categories
     *
     * @return Shop
     */
    public function setCategories($categories): Shop
    {
        $this->categories = $categories;

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
     * @return Shop
     */
    public function setDeliveryOptions($deliveryOptions): Shop
    {
        $this->deliveryOptions = $deliveryOptions;

        return $this;
    }

    /**
     * @return Collection|Offer[]
     */
    public function getOffers()
    {
        return $this->offers;
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
}
