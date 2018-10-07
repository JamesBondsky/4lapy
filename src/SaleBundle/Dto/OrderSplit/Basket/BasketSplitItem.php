<?php

namespace FourPaws\SaleBundle\Dto\OrderSplit\Basket;

class BasketSplitItem
{
    /**
     * @var int
     */
    protected $productId;

    /**
     * @var int
     */
    protected $amount = 0;

    /**
     * @var float
     */
    protected $price = 0;

    /**
     * @var float
     */
    protected $basePrice = 0;

    /**
     * @var float
     */
    protected $discount = 0;

    /**
     * @var bool
     */
    protected $gift = false;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     * @return BasketSplitItem
     */
    public function setProductId(int $productId): BasketSplitItem
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return BasketSplitItem
     */
    public function setAmount(int $amount): BasketSplitItem
    {
        $this->amount = $amount;
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
     * @return BasketSplitItem
     */
    public function setPrice(float $price): BasketSplitItem
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return float
     */
    public function getBasePrice(): float
    {
        return $this->basePrice;
    }

    /**
     * @param float $basePrice
     * @return BasketSplitItem
     */
    public function setBasePrice(float $basePrice): BasketSplitItem
    {
        $this->basePrice = $basePrice;
        return $this;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     * @return BasketSplitItem
     */
    public function setDiscount(float $discount): BasketSplitItem
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @return bool
     */
    public function isGift(): bool
    {
        return $this->gift;
    }

    /**
     * @param bool $gift
     *
     * @return BasketSplitItem
     */
    public function setGift(bool $gift): BasketSplitItem
    {
        $this->gift = $gift;

        return $this;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     * @return BasketSplitItem
     */
    public function setProperties(array $properties): BasketSplitItem
    {
        $this->properties = $properties;
        return $this;
    }
}
