<?php

namespace FourPaws\StoreBundle\Collection;

use FourPaws\StoreBundle\Entity\Schedule;
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
     * @return Schedule
     */
    public function getTotalSchedule(): Schedule
    {
        $from = null;
        $to = null;

        /** @var Store $item */
        foreach ($this->getIterator() as $item) {
            $schedule = $item->getScheduleString();
            if ((null === $from) || ($schedule->getFrom() < $from)) {
                $from = $schedule->getFrom();
            }
            if ((null === $to) || ($schedule->getTo() > $to)) {
                $to = $schedule->getTo();
            }
        }

        return (new Schedule())->setFrom($from)->setTo($to);
    }
}
