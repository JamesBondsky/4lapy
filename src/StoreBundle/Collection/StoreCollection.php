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
     * @return array
     */
    public function getTotalSchedule(): array
    {
        /** @var Store $item */
        $from = null;
        $to = null;
        foreach ($this->getIterator() as $item) {
            $formattedSchedule = $item->getFormattedSchedule();
            if ((null === $from) || ($formattedSchedule['from'] < $from)) {
                $from = $formattedSchedule['from'];
            }
            if ((null === $to) || ($formattedSchedule['to'] > $to)) {
                $to = $formattedSchedule['to'];
            }
        }

        return [
            'from' => $from,
            'to'   => $to,
        ];
    }
}
