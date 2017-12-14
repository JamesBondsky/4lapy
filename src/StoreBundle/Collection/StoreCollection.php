<?php

namespace FourPaws\StoreBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\StoreBundle\Entity\Store;

class StoreCollection extends ArrayCollection
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
