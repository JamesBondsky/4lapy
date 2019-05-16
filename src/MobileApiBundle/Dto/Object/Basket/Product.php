<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Basket;

use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use FourPaws\MobileApiBundle\Dto\Object\PriceWithQuantity;
use JMS\Serializer\Annotation as Serializer;

class Product
{
    /**
     * @Serializer\SerializedName("basketItemId")
     * @Serializer\Type("integer")
     * @var int
     */
    protected $basketItemId = 0;

    /**
     * @Serializer\SerializedName("goods")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct")
     * @var ShortProduct
     */
    protected $shortProduct;

    /**
     * @Serializer\SerializedName("qty")
     * @Serializer\Type("integer")
     * @var int
     */
    protected $quantity = 0;

    /**
     * @Serializer\SerializedName("prices")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\PriceWithQuantity>")
     * @var PriceWithQuantity[]
     */
    protected $prices = [];


    /**
     * @return int
     */
    public function getBasketItemId(): int
    {
        return $this->basketItemId;
    }

    /**
     * @param int $basketItemId
     *
     * @return Product
     */
    public function setBasketItemId(int $basketItemId): Product
    {
        $this->basketItemId = $basketItemId;
        return $this;
    }

    /**
     * @return null|ShortProduct
     */
    public function getShortProduct()
    {
        return $this->shortProduct;
    }

    /**
     * @param ShortProduct $shortProduct
     *
     * @return Product
     */
    public function setShortProduct(ShortProduct $shortProduct): Product
    {
        $this->shortProduct = $shortProduct;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return Product
     */
    public function setQuantity(int $quantity): Product
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return PriceWithQuantity[]
     */
    public function getPrices(): array
    {
        return $this->prices;
    }

    /**
     * @param PriceWithQuantity[] $prices
     * @return Product
     */
    public function setPrices(array $prices): Product
    {
        $this->prices = $prices;
        return $this;
    }
}
