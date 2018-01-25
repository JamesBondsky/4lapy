<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Basket;

use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use JMS\Serializer\Annotation as Serializer;

class Product
{
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
     * @return ShortProduct
     */
    public function getShortProduct(): ShortProduct
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
}
