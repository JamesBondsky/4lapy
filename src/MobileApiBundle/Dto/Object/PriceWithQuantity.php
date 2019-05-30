<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class PriceWithQuantity
 *
 * @package FourPaws\MobileApiBundle\Dto\Object
 *
 * ОбъектЦенаКоличество
 */
class PriceWithQuantity
{
    /**
     * ОбъектЦена
     *
     * @var Price
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Price")
     * @Serializer\SerializedName("price")
     */
    protected $price;

    /**
     * @Serializer\SerializedName("qty")
     * @Serializer\Type("integer")
     * @var int
     */
    protected $quantity = 0;

    /**
     * @return Price
     */
    public function getPrice(): Price
    {
        return $this->price;
    }

    /**
     * @param Price $price
     *
     * @return $this
     */
    public function setPrice(Price $price)
    {
        $this->price = $price;
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
     * @return PriceWithQuantity
     */
    public function setQuantity(int $quantity): PriceWithQuantity
    {
        $this->quantity = $quantity;
        return $this;
    }
}
