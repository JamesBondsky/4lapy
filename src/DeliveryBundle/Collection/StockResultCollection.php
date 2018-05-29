<?php

namespace FourPaws\DeliveryBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Entity\StockResult;
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

    public function getOrderable(): StockResultCollection
    {
        return $this->filter(
            function (StockResult $stockResult) {
                return \in_array(
                    $stockResult->getType(),
                    [StockResult::TYPE_DELAYED, StockResult::TYPE_AVAILABLE],
                    true
                );
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
     * @param bool $strict
     *
     * @return StockResultCollection
     */
    public function getRegular(bool $strict = false): StockResultCollection
    {
        return $this->filter(
            function (StockResult $stockResult) use ($strict) {
                $isByRequest = $stockResult->getOffer()->isByRequest();
                return  !$isByRequest || (!$strict && $stockResult->getType() === StockResult::TYPE_AVAILABLE);
            }
        );
    }

    /**
     * @param bool $strict
     *
     * @return StockResultCollection
     */
    public function getByRequest(bool $strict = false): StockResultCollection
    {
        return $this->filter(
            function (StockResult $stockResult) use ($strict) {
                $isByRequest = $stockResult->getOffer()->isByRequest();

                return $isByRequest && ($strict || ($stockResult->getType() === StockResult::TYPE_DELAYED));
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
                return $stockResult->getStore()->getXmlId() === $store->getXmlId();
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
        return $this->filterByOfferId($offer->getId());
    }

    /**
     * @param int $id
     *
     * @return StockResultCollection
     */
    public function filterByOfferId(int $id): StockResultCollection
    {
        return $this->filter(
            function (StockResult $stockResult) use ($id) {
                return $stockResult->getOffer()->getId() === $id;
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
     */
    public function getStores($skipUnavailable = true): StoreCollection
    {
        $result = new StoreCollection();
        /** @var StockResult $item */
        foreach ($this->getIterator() as $item) {
            if ($skipUnavailable && $item->getType() === StockResult::TYPE_UNAVAILABLE) {
                continue;
            }

            $store = $item->getStore();
            $result[$store->getXmlId()] = $store;
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

            $price += $item->getPrice();
        }

        return $price;
    }

    /**
     * @param $type
     *
     * @return StockResultCollection
     */
    public function setType($type): StockResultCollection
    {
        /** @var StockResult $item */
        foreach ($this->getIterator() as $item) {
            $item->setType($type);
        }

        return $this;
    }

    public function __clone()
    {
        foreach ($this->getIterator() as $i => $item) {
            $this[$i] = clone $item;
        }
    }
}
