<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\Fiscalization;


class Item
{
    /**
     * @var int
     */
    protected $positionId = 0;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var ItemQuantity
     */
    protected $quantity;

    /**
     * @var int
     */
    protected $total = 0;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var int
     */
    protected $price = 0;

    /**
     * @var ItemTax
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