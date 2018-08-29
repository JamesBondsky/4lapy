<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\Fiscalization;

use JMS\Serializer\Annotation as Serializer;

class Item
{
    /**
     * @var int
     *
     * @Serializer\SerializedName("positionId")
     * @Serializer\Type("int")
     */
    protected $positionId = 0;

    /**
     * @var string
     *
     * @Serializer\SerializedName("name")
     * @Serializer\Type("string")
     */
    protected $name = '';

    /**
     * @var string
     *
     * @Serializer\Exclude()
     */
    protected $xmlId = '';

    /**
     * @var ItemQuantity
     *
     * @Serializer\SerializedName("quantity")
     * @Serializer\Type("FourPaws\SaleBundle\Dto\Fiscalization\ItemQuantity")
     */
    protected $quantity;

    /**
     * @var int
     *
     * @Serializer\SerializedName("itemAmount")
     * @Serializer\Type("integer")
     */
    protected $total = 0;

    /**
     * @var string
     *
     * @Serializer\SerializedName("itemCode")
     * @Serializer\Type("string")
     */
    protected $code;

    /**
     * @var int
     *
     * @Serializer\SerializedName("itemPrice")
     * @Serializer\Type("string")
     */
    protected $price;

    /**
     * @var ItemTax
     *
     * @Serializer\SerializedName("tax")
     * @Serializer\Type("FourPaws\SaleBundle\Dto\Fiscalization\ItemTax")
     */
    protected $tax;

    /**
     * @return int
     */
    public function getPositionId(): int
    {
        return $this->positionId;
    }

    /**
     * @param int $positionId
     * @return Item
     */
    public function setPositionId(int $positionId): Item
    {
        $this->positionId = $positionId;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Item
     */
    public function setName(string $name): Item
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getXmlId(): string
    {
        return $this->xmlId;
    }

    /**
     * @param string $xmlId
     * @return Item
     */
    public function setXmlId(string $xmlId): Item
    {
        $this->xmlId = $xmlId;
        return $this;
    }

    /**
     * @return ItemQuantity
     */
    public function getQuantity(): ItemQuantity
    {
        return $this->quantity;
    }

    /**
     * @param ItemQuantity $quantity
     * @return Item
     */
    public function setQuantity(ItemQuantity $quantity): Item
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param int $total
     * @return Item
     */
    public function setTotal(int $total): Item
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Item
     */
    public function setCode(string $code): Item
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     * @return Item
     */
    public function setPrice(int $price): Item
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return ItemTax
     */
    public function getTax(): ItemTax
    {
        return $this->tax;
    }

    /**
     * @param ItemTax $tax
     * @return Item
     */
    public function setTax(ItemTax $tax): Item
    {
        $this->tax = $tax;
        return $this;
    }
}
