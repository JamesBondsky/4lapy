<?php

namespace FourPaws\CatalogBundle\Dto\RetailRocket;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Collections\ArrayCollection;
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
     * Оффсет
     *
     * @Serializer\SkipWhenEmpty()
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $offset;

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
     * Торговые предложения
     *
     * @Serializer\XmlList(inline=false, entry="offer")
     * @Serializer\Type("ArrayCollection<FourPaws\CatalogBundle\Dto\Yandex\Offer>")
     *
     * @var Offer[]|Collection
     */
    protected $offers;

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
     * @return int
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     *
     * @return Shop
     */
    public function setOffset(?int $offset): Shop
    {
        $this->offset = $offset;

        return $this;
    }
}
