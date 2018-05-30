<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Entity;

class PriceForAmount
{
    /**
     * @var float
     */
    protected $price = 0;

    /**
     * @var int
     */
    protected $amount = 0;

    /**
     * @var string
     */
    protected $basketCode = '';

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return PriceForAmount
     */
    public function setPrice(float $price): PriceForAmount
    {
        $this->price = $price;
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
     * @return PriceForAmount
     */
    public function setAmount(int $amount): PriceForAmount
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getBasketCode(): string
    {
        return $this->basketCode;
    }

    /**
     * @param string $basketCode
     * @return PriceForAmount
     */
    public function setBasketCode(string $basketCode): PriceForAmount
    {
        $this->basketCode = $basketCode;
        return $this;
    }
}