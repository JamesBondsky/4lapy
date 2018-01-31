<?php

namespace FourPaws\DeliveryBundle\Entity;

use FourPaws\Catalog\Model\Offer;
use FourPaws\StoreBundle\Collection\StoreCollection;
use \DateTime;

class StockResult
{
    const TYPE_AVAILABLE = 'available';

    const TYPE_DELAYED = 'delayed';

    const TYPE_UNAVAILABLE = 'unavailable';

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
     * @var StoreCollection
     */
    protected $stores;

    /**
     * Склады, откуда будет поставка на $stores
     *
     * @var StoreCollection
     */
    protected $delayStores;

    /**
     * @var float
     */
    protected $price = 0;

    /**
     * @var string
     */
    protected $type = self::TYPE_AVAILABLE;

    /**
     * @var null|DateTime
     */
    protected $deliveryDate;

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
     * @return StoreCollection
     */
    public function getStores(): StoreCollection
    {
        if (!$this->stores) {
            $this->stores = new StoreCollection();
        }

        return $this->stores;
    }

    /**
     * @param StoreCollection $stores
     *
     * @return StockResult
     */
    public function setStores(StoreCollection $stores): StockResult
    {
        $this->stores = $stores;

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
     * @return DateTime
     */
    public function getDeliveryDate(): DateTime
    {
        return $this->deliveryDate;
    }

    /**
     * @param DateTime $deliveryDate
     *
     * @return StockResult
     */
    public function setDeliveryDate(DateTime $deliveryDate): StockResult
    {
        $this->deliveryDate = $deliveryDate;

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

    /**
     * @return StoreCollection
     */
    public function getDelayStores(): StoreCollection
    {
        if (!$this->delayStores) {
            $this->delayStores = new StoreCollection();
        }

        return $this->delayStores;
    }

    /**
     * @param StoreCollection $delayStores
     *
     * @return StockResult
     */
    public function setDelayStores(StoreCollection $delayStores): StockResult
    {
        $this->delayStores = $delayStores;

        return $this;
    }
}
