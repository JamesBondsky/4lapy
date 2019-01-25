<?php

namespace FourPaws\CatalogBundle\Dto\Yandex;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Purchase
 *
 * @package FourPaws\CatalogBundle\Dto\Yandex
 *
 * @Serializer\XmlRoot("purchase")
 */
class Purchase
{
    /**
     * @Serializer\XmlElement(cdata=false)
     *
     * @var string
     */
    protected $requiredQuantity;

    /**
     * @Serializer\XmlElement(cdata=false)
     *
     * @var string
     */
    protected $freeQuantity;

    /**
     * Продукты
     *
     * @Serializer\XmlList(inline=true, entry="product")
     * @Serializer\Type("ArrayCollection<FourPaws\CatalogBundle\Dto\Yandex\Product>")
     * @Serializer\SkipWhenEmpty()
     *
     * @var Product[]|Collection
     */
    protected $product;

    /**
     * @return string
     */
    public function getRequiredQuantity(): string
    {
        return $this->requiredQuantity;
    }

    /**
     * @param string $requiredQuantity
     * @return Purchase
     */
    public function setRequiredQuantity(string $requiredQuantity): Purchase
    {
        $this->requiredQuantity = $requiredQuantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getFreeQuantity(): string
    {
        return $this->freeQuantity;
    }

    /**
     * @param string $freeQuantity
     * @return Purchase
     */
    public function setFreeQuantity(string $freeQuantity): Purchase
    {
        $this->freeQuantity = $freeQuantity;

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Collection|Product[] $product
     * @return Purchase
     */
    public function setProduct($product): Purchase
    {
        $this->product = $product;

        return $this;
    }
}
