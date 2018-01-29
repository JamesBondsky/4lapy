<?php

namespace FourPaws\DeliveryBundle\Entity;

use FourPaws\Catalog\Model\Offer;
use FourPaws\StoreBundle\Collection\StoreCollection;

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
     * @var StoreCollection
     */
    protected $stores;

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
}
