<?php

namespace FourPaws\SaleBundle\Dto\ShopList;

use JMS\Serializer\Annotation as Serializer;

class Offer
{
    /**
     * @var int
     *
     * @Serializer\SerializedName("id")
     * @Serializer\Type("int")
     */
    protected $id;

    /**
     * @var int
     *
     * @Serializer\SerializedName("quantity")
     * @Serializer\Type("int")
     */
    protected $quantity;

    /**
     * @var float
     *
     * @Serializer\SerializedName("price")
     * @Serializer\Type("float")
     */
    protected $price;

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
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return Offer
     */
    public function setQuantity(int $quantity): Offer
    {
        $this->quantity = $quantity;

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
}
