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
}
