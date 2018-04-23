<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Collection;

use Bitrix\Main\ArgumentException;
use FourPaws\StoreBundle\Entity\DeliverySchedule;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;

/**
 * Class DeliveryScheduleCollection
 */
class DeliveryScheduleCollection extends BaseCollection
{
    /**
     * @param StoreCollection $stores
     * @return DeliveryScheduleCollection
     */
    public function filterBySenders(StoreCollection $stores): DeliveryScheduleCollection
    {
        $xmlIds = [];
        /** @var Store $store */
        foreach ($stores as $store) {
            $xmlIds[] = $store->getXmlId();
        }

        return $this->filter(function (DeliverySchedule $schedule) use ($xmlIds) {
            return \in_array($schedule->getSenderCode(), $xmlIds, true);
        });
    }

    /**
     * @param StoreCollection $stores
     * @return DeliveryScheduleCollection
     */
    public function filterByReceivers(StoreCollection $stores): DeliveryScheduleCollection
    {
        $xmlIds = [];
        /** @var Store $store */
        foreach ($stores as $store) {
            $xmlIds[] = $store->getXmlId();
        }

        return $this->filter(function (DeliverySchedule $schedule) use ($xmlIds) {
            return \in_array($schedule->getReceiverCode(), $xmlIds, true);
        });
    }

    /**
     * @return StoreCollection
     * @throws NotFoundException
     * @throws ArgumentException
     */
    public function getSenders(): StoreCollection
    {
        $result = new StoreCollection();

        /** @var DeliverySchedule $item */
        foreach ($this->getIterator() as $item) {
            if (isset($result[$item->getSenderCode()])) {
                continue;
            }
            $store = $item->getSender();
            $result[$store->getXmlId()] = $store;
        }

        return $result;
    }

    /**
     * @return StoreCollection
     * @throws NotFoundException
     * @throws ArgumentException
     */
    public function getReceivers(): StoreCollection
    {
        $result = new StoreCollection();

        /** @var DeliverySchedule $item */
        foreach ($this->getIterator() as $item) {
            if (isset($result[$item->getReceiverCode()])) {
                continue;
            }
            $store = $item->getReceiver();
            $result[$store->getXmlId()] = $store;
        }

        return $result;
    }

    /**
     * @param \DateTime|null $date
     * @return DeliveryScheduleCollection
     */
    public function getActive(?\DateTime $date): DeliveryScheduleCollection
    {
        if (!$date instanceof \DateTime) {
            $date = new \DateTime();
        }
        return $this->filter(function (DeliverySchedule $schedule) use ($date) {
            return $schedule->isActiveForDate($date);
        });
    }
}
