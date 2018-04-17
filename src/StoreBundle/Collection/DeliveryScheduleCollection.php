<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Collection;

use Bitrix\Main\ArgumentException;
use FourPaws\StoreBundle\Entity\DeliverySchedule;
use FourPaws\StoreBundle\Entity\DeliveryScheduleResult;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

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

    /**
     * Получение ближайшего графика поставок для указанной даты
     *
     * @param Store $receiver
     * @param null|\DateTime $from
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @return null|DeliveryScheduleResult
     */
    public function getNextDelivery(
        Store $receiver,
        \DateTime $from = null
    ): ?DeliveryScheduleResult {
        if (!$from) {
            $from = new \DateTime();
        }

        /** @var DeliveryScheduleResult $result */
        $result = null;
        $senderSchedules = $this->getActive($from);
        /** @var DeliverySchedule $senderSchedule */

        foreach ($senderSchedules as $senderSchedule) {
            if (!$date = $this->doGetNextDelivery($senderSchedule, $receiver, $from)) {
                continue;
            }

            if (null === $result || $result->getDate() > $date) {
                $result = (new DeliveryScheduleResult())->setDate($date)->setSchedule($senderSchedule);
            }
        }

        return $result;
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param DeliverySchedule $senderSchedule
     * @param Store $receiver
     * @param \DateTime $from
     * @param int $transitionCount
     * @throws ArgumentException
     * @throws NotFoundException
     * @return null|\DateTime
     */
    protected function doGetNextDelivery(
        DeliverySchedule $senderSchedule,
        Store $receiver,
        \DateTime $from,
        int $transitionCount = 0
    ): ?\DateTime {
        /** Пока не нужно */
        if ($transitionCount > 0) {
            return null;
        }

        if (!$nextDelivery = $senderSchedule->getNextDelivery($from)) {
            return null;
        }

        if ($senderSchedule->getReceiverCode() === $receiver->getXmlId()) {
            return $nextDelivery;
        }

        $children = $senderSchedule->getReceiverSchedules()->getActive($nextDelivery);
        /** @var DeliverySchedule $child */
        foreach ($children as $child) {
            if ($child->getReceiverCode() === $receiver->getXmlId()) {
                $childFrom = $child->getNextDelivery($nextDelivery);
                if ($childFrom) {
                    $results[] = $childFrom;
                }
            } else {
                if ($childFrom = $child->getNextDelivery($nextDelivery)) {
                    $results[] = $this->doGetNextDelivery(
                        $child,
                        $receiver,
                        $childFrom,
                        $transitionCount++
                    );
                }
            }
        }

        return empty($results) ? null : min($results);
    }
}
