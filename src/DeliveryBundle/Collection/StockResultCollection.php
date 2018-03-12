<?php

namespace FourPaws\DeliveryBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;

class StockResultCollection extends ArrayCollection
{
    /**
     * @return StockResultCollection
     */
    public function getAvailable(): StockResultCollection
    {
        return $this->filter(
            function (StockResult $stockResult) {
                return $stockResult->getType() === StockResult::TYPE_AVAILABLE;
            }
        );
    }

    /**
     * @return StockResultCollection
     */
    public function getDelayed(): StockResultCollection
    {
        return $this->filter(
            function (StockResult $stockResult) {
                return $stockResult->getType() === StockResult::TYPE_DELAYED;
            }
        );
    }

    /**
     * @return StockResultCollection
     */
    public function getUnavailable(): StockResultCollection
    {
        return $this->filter(
            function (StockResult $stockResult) {
                return $stockResult->getType() === StockResult::TYPE_UNAVAILABLE;
            }
        );
    }

    /**
     * @param Store $store
     *
     * @return StockResultCollection
     */
    public function filterByStore(Store $store): StockResultCollection
    {
        return $this->filter(
            function (StockResult $stockResult) use ($store) {
                return $stockResult->getStores()->exists(
                    function ($i, Store $stockResultStore) use ($store) {
                        return $stockResultStore->getXmlId() === $store->getXmlId();
                    }
                );
            }
        );
    }

    /**
     * @param Offer $offer
     *
     * @return StockResultCollection
     */
    public function filterByOffer(Offer $offer): StockResultCollection
    {
        return $this->filter(
            function (StockResult $stockResult) use ($offer) {
                return $stockResult->getOffer()->getId() === $offer->getId();
            }
        );
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        $amount = 0;
        /** @var StockResult $item */
        foreach ($this->getIterator() as $item) {
            $amount += $item->getAmount();
        }

        return $amount;
    }

    /**
     * @param bool $skipUnavailable
     *
     * @return StoreCollection
     * @throws NotFoundException
     */
    public function getStores($skipUnavailable = true): StoreCollection
    {
        $result = new StoreCollection();
        /** @var StockResult $item */
        foreach ($this->getIterator() as $item) {
            if ($skipUnavailable && $item->getType() === StockResult::TYPE_UNAVAILABLE) {
                continue;
            }

            $stores = $item->getStores();
            /** @var Store $store */
            foreach ($stores as $store) {
                if (!isset($result[$store->getXmlId()])) {
                    $result[$store->getXmlId()] = $store;
                }
            }
        }

        if ($result->isEmpty()) {
            throw new NotFoundException('No stores found');
        }

        return $result;
    }

    /**
     * @param bool $skipUnavailable
     *
     * @return ArrayCollection
     */
    public function getOffers($skipUnavailable = true): ArrayCollection
    {
        $result = new ArrayCollection();
        /** @var StockResult $item */
        foreach ($this->getIterator() as $item) {
            if ($skipUnavailable && $item->getType() === StockResult::TYPE_UNAVAILABLE) {
                continue;
            }

            $offer = $item->getOffer();
            if (!isset($result[$offer->getId()])) {
                $result[$offer->getId()] = $offer;
            }
        }

        return $result;
    }

    /**
     * @param bool $skipUnavailable
     *
     * @return float
     */
    public function getPrice($skipUnavailable = true): float
    {
        $price = 0;
        /** @var StockResult $item */
        foreach ($this->getIterator() as $item) {
            if ($skipUnavailable && $item->getType() === StockResult::TYPE_UNAVAILABLE) {
                continue;
            }

            $price += $item->getPrice() * $item->getAmount();
        }

        return $price;
    }
}
