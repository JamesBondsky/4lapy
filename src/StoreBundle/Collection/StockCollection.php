<?php

namespace FourPaws\StoreBundle\Collection;

use FourPaws\StoreBundle\Entity\Stock;

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
        return $this->filter(
            function (Stock $stock) use ($stores) {
                $ids = [];
                foreach ($stores as $store) {
                    $ids[] = $store->getId();
                }

                return in_array($stock->getStoreId(), $ids);
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
                return $stock->getProductId() == $offerId;
            }
        );
    }

    /**
     * @return int
     */
    public function getTotalAmount(): int
    {
        $amount = 0;
        /** @var Stock $stock */
        foreach ($this->getIterator() as $stock) {
            $amount += $stock->getAmount();
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
        /** @var Stock $stock */
        foreach ($stocks->getIterator() as $stock) {
            $amount += $stock->getAmount();
        }

        return $amount;
    }
}
