<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Collection;

use FourPaws\StoreBundle\Entity\DeliverySchedule;
use FourPaws\StoreBundle\Entity\DeliveryScheduleResult;
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
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
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
    }

    /**
     * @param StoreCollection $receivers
     * @param \DateTime|null $from
     * @return DeliveryScheduleResultCollection
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getNextDeliveries(
        StoreCollection $receivers,
        \DateTime $from = null
    ): DeliveryScheduleResultCollection {
        if (!$from) {
            $from = new \DateTime();
        }

        $result = new DeliveryScheduleResultCollection();

        /** @var Store $receiver */
        foreach ($receivers as $receiver) {
            if ($res = $this->getNextDelivery($receiver, $from)) {
                $result->add($res);
            }
        }

        return $result;
    }
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param DeliverySchedule $senderSchedule
     * @param Store $receiver
     * @param \DateTime $from
     * @param int $transitionCount
     * @throws \Bitrix\Main\ArgumentException
     * @throws NotFoundException
     * @return null|\DateTime
     */
    protected function doGetNextDelivery(
        DeliverySchedule $senderSchedule,
        Store $receiver,
        \DateTime $from,
        int $transitionCount = 0
    ): ?\DateTime {
        if ($transitionCount > 3) {
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
