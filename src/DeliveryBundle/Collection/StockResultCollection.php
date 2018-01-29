<?php

namespace FourPaws\DeliveryBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Entity\StockResult;
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
                return $stockResult->getStores()->exists(function ($i, Store $stockResultStore) use ($store) {
                    return $stockResultStore->getId() === $store->getId();
                });
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
     * @return \DateTime
     */
    public function getDeliveryDate()
    {
        $deliveryDate = new \DateTime();

        /** @var StockResult $item */
        foreach ($this->getIterator() as $item) {
            $deliveryDate = \max($deliveryDate, $item->getDeliveryDate());
        }

        return $deliveryDate;
    }
}
