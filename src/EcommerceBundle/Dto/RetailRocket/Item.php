<?php

namespace FourPaws\EcommerceBundle\Dto\RetailRocket;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Item
 *
 * @package FourPaws\EcommerceBundle\Dto\RetailRocket
 */
class Item
{
    /**
     * Id товара
     *
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $id;

    /**
     * Цена
     *
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $price;

    /**
     * Количество
     *
     * @Serializer\Type("int")
     * @Serializer\SerializedName("qnt")
     *
     * @var int
     */
    protected $quantity = 0;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return Item
     */
    public function setId(string $id): Item
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param float $price
     *
     * @return Item
     */
    public function setPrice(float $price): Item
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
     * @return Item
     */
    public function setQuantity(int $quantity): Item
    {
        $this->quantity = $quantity;

        return $this;
    }
}
