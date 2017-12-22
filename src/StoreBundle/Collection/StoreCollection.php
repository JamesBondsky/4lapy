<?php

namespace FourPaws\StoreBundle\Collection;

use FourPaws\StoreBundle\Entity\Store;

class StoreCollection extends BaseCollection
{
    public function getStores()
    {
        return $this->filter(
            function (Store $store) {
                return !$store->isShop();
            }
        );
    }

    public function getShops()
    {
        return $this->filter(
            function (Store $store) {
                return $store->isShop();
            }
        );
    }
}
