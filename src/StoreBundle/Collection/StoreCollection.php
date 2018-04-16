<?php

namespace FourPaws\StoreBundle\Collection;

use FourPaws\StoreBundle\Entity\Store;

class StoreCollection extends BaseCollection
{
    /**
     * @return StoreCollection
     */
    public function getStores(): StoreCollection
    {
        return $this->filter(
            function (Store $store) {
                return !$store->isShop();
            }
        );
    }

    /**
     * @return StoreCollection
     */
    public function getShops(): StoreCollection
    {
        return $this->filter(
            function (Store $store) {
                return $store->isShop();
            }
        );
    }

    /**
     * @return StoreCollection
     */
    public function getBaseShops(): StoreCollection
    {
        return $this->filter(
            function (Store $store) {
                return $store->isShop() && $store->isBase();
            }
        );
    }

    /**
     * @param StoreCollection $stores
     * @return StoreCollection
     */
    public function excludeStores(StoreCollection $stores): StoreCollection
    {
        $filter = function (
            /** @noinspection PhpUnusedParameterInspection */
            string $key, Store $store) use ($stores) {
            return $stores->hasStore($store);
        };
        $result = new static();
        /** @var Store $store */
        foreach ($this->getIterator() as $store) {
            if ($this->exists($filter)) {
                continue;
            }

            $result[$store->getXmlId()] = $store;
        }

        return $result;
    }

    /**
     * @param Store $store
     *
     * @return StoreCollection
     */
    public function excludeStore(Store $store): StoreCollection
    {
       return $this->filter(function (Store $existingStore) use ($store) {
           return $existingStore->getXmlId() !== $store->getXmlId();
       });
    }

    /**
     * @param Store $store
     * @return bool
     */
    public function hasStore(Store $store): bool
    {
        return !$this->filter(function (Store $currentStore) use ($store) {
            return $currentStore->getXmlId() === $store->getXmlId();
        })->isEmpty();
    }
}
