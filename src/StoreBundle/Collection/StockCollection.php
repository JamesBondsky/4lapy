<?php

namespace FourPaws\StoreBundle\Collection;

use FourPaws\StoreBundle\Entity\Stock;
use FourPaws\StoreBundle\Entity\Store;

class StockCollection extends BaseCollection
{
    /**
     * Возвращает коллекцию остатков для переданных складов
     *
     * @param StoreCollection $stores
     *
     * @return StockCollection
     */
    public function filterByStores(StoreCollection $stores): StockCollection
    {
        $ids = [];
        foreach ($stores as $store) {
            $ids[] = $store->getId();
        }

        return $this->filter(
            function (Stock $stock) use ($ids) {
                return \in_array($stock->getStoreId(), $ids, true);
            }
        );
    }

    /**
     * @param Store $store
     *
     * @return StockCollection
     */
    public function filterByStore(Store $store): StockCollection
    {
        return $this->filter(
            function (Stock $stock) use ($store) {
                return $stock->getStoreId() === $store->getId();
            }
        );
    }

    /**
     * @param $offerId
     *
     * @return StockCollection
     */
    public function filterByOfferId($offerId): StockCollection
    {
        return $this->filter(
            function (Stock $stock) use ($offerId) {
                return $stock->getProductId() === $offerId;
            }
        );
    }

    /**
     * @return int
     */
    public function getTotalAmount(): int
    {
        $amount = 0;
        /** @var Stock $item */
        foreach ($this->getIterator() as $item) {
            $amount += $item->getAmount();
        }

        return $amount;
    }

    /**
     * @param $offerId
     *
     * @return int
     */
    public function getAmountByOfferId($offerId): int
    {
        $stocks = $this->filterByOfferId($offerId);
        $amount = 0;

        /** @var Stock $item */
        foreach ($stocks as $item) {
            $amount += $item->getAmount();
        }

        return $amount;
    }
}
