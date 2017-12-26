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
}
