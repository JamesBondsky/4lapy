<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\SberbankOrderInfo\OrderBundle;

use JMS\Serializer\Annotation as Serializer;

class Item
{
    /**
     * @var string
     *
     * @Serializer\SerializedName("positionId")
     * @Serializer\Type("string")
     */
    protected $positionId = '';

    /**
     * @var string
     *
     * @Serializer\SerializedName("name")
     * @Serializer\Type("string")
     */
    protected $name = '';

    /**
     * @var ItemQuantity
     *
     * @Serializer\SerializedName("quantity")
     * @Serializer\Type("FourPaws\SaleBundle\Dto\SberbankOrderInfo\OrderBundle\ItemQuantity")
     */
    protected $quantity;

    /**
     * @var int
     *
     * @Serializer\SerializedName("itemAmount")
     * @Serializer\Type("int")
     */
    protected $itemAmount = 0;

    /**
     * @var int
     *
     * @Serializer\SerializedName("itemCurrency")
     * @Serializer\Type("int")
     */
    protected $itemCurrency = 0;

    /**
     * @var string
     *
     * @Serializer\SerializedName("itemCode")
     * @Serializer\Type("string")
     */
    protected $itemCode = '';

    /**
     * @return string
     */
    public function getPositionId(): string
    {
        return $this->positionId;
    }

    /**
     * @param string $positionId
     * @return Item
     */
    public function setPositionId(string $positionId): Item
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
    public function getItemAmount(): int
    {
        return $this->itemAmount;
    }

    /**
     * @param int $itemAmount
     * @return Item
     */
    public function setItemAmount(int $itemAmount): Item
    {
        $this->itemAmount = $itemAmount;

        return $this;
    }

    /**
     * @return int
     */
    public function getItemCurrency(): int
    {
        return $this->itemCurrency;
    }

    /**
     * @param int $itemCurrency
     * @return Item
     */
    public function setItemCurrency(int $itemCurrency): Item
    {
        $this->itemCurrency = $itemCurrency;

        return $this;
    }

    /**
     * @return string
     */
    public function getItemCode(): string
    {
        return $this->itemCode;
    }

    /**
     * @param string $itemCode
     * @return Item
     */
    public function setItemCode(string $itemCode): Item
    {
        $this->itemCode = $itemCode;

        return $this;
    }
}
