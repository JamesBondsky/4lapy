<?php

namespace FourPaws\StoreBundle\Collection;

use FourPaws\Catalog\Model\Offer;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\StoreBundle\Entity\Stock;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use function in_array;

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
            static function (Stock $stock) use ($ids) {
                return in_array($stock->getStoreId(), $ids, true);
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
            static function (Stock $stock) use ($store) {
                return $stock->getStoreId() === $store->getId();
            }
        );
    }

    /**
     * @param Offer $offer
     * @return StockCollection
     */
    public function filterByOffer(Offer $offer): StockCollection
    {
        return $this->filter(
            static function (Stock $stock) use ($offer) {
                return $stock->getProductId() === $offer->getId();
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
     * @return int
     * @throws NotFoundException
     */
    public function getTotalAmountDC(): int
    {
        $amount = 0;
        /** @var Stock $item */
        foreach ($this->getIterator() as $item) {
            if ($item->getStore()->getXmlId() === OrderService::STORE) {
                $amount += $item->getAmount();
                break;
            }
        }

        return $amount;
    }

    /**
     * @param Offer $offer
     * @return int
     */
    public function getAmountByOffer(Offer $offer): int
    {
        $stocks = $this->filterByOffer($offer);
        $amount = 0;

        /** @var Stock $item */
        foreach ($stocks as $item) {
            $amount += $item->getAmount();
        }

        return $amount;
    }

    /**
     * Получение складов с наличием >= указанному
     *
     * @param int $amount
     * @return StoreCollection
     * @throws NotFoundException
     */
    public function getStores(int $amount = 0): StoreCollection
    {
        $result = [];
        /** @var Stock $item */
        foreach ($this->getIterator() as $item) {
            if ($item->getAmount() < $amount) {
                continue;
            }
            $store = $item->getStore();
            $result[$store->getXmlId()] = $store;
        }

        return new StoreCollection($result);
    }
}
