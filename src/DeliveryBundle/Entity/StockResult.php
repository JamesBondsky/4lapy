<?php

namespace FourPaws\DeliveryBundle\Entity;

use FourPaws\Catalog\Model\Offer;
use FourPaws\StoreBundle\Entity\Store;

class StockResult
{
    public const TYPE_AVAILABLE = 'available';

    public const TYPE_DELAYED = 'delayed';

    public const TYPE_UNAVAILABLE = 'unavailable';

    /**
     * @var int
     */
    protected $amount;

    /**
     * @var Offer
     */
    protected $offer;

    /**
     * Склады, откуда будет осуществляться доставка/самовывоз
     *
     * @var Store
     */
    protected $store;

    /**
     * @var float
     */
    protected $price = 0;

    /**
     * @var string
     */
    protected $type = self::TYPE_AVAILABLE;

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     *
     * @return StockResult
     */
    public function setAmount(int $amount): StockResult
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return Offer
     */
    public function getOffer(): Offer
    {
        return $this->offer;
    }

    /**
     * @param Offer $offer
     *
     * @return StockResult
     */
    public function setOffer(Offer $offer): StockResult
    {
        $this->offer = $offer;

        return $this;
    }

    /**
     * @return Store
     */
    public function getStore(): Store
    {
        return $this->store;
    }

    /**
     * @param Store $stores
     *
     * @return StockResult
     */
    public function setStore(Store $stores): StockResult
    {
        $this->store = $stores;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return StockResult
     */
    public function setType(string $type): StockResult
    {
        $this->type = $type;

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
     * @return StockResult
     */
    public function setPrice(float $price): StockResult
    {
        $this->price = $price;

        return $this;
    }
}
