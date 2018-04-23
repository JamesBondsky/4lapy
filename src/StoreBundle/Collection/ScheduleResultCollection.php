<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Collection;

use Bitrix\Main\ArgumentException;
use FourPaws\StoreBundle\Entity\ScheduleResult;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;

class ScheduleResultCollection extends BaseCollection
{
    /**
     * @return StoreCollection
     * @throws ArgumentException
     * @throws NotFoundException
     */
    public function getSenders(): StoreCollection
    {
        $result = new StoreCollection();
        /** @var ScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            if (isset($result[$item->getSenderCode()])) {
                continue;
            }

            $result[$item->getSenderCode()] = $item->getSender();
        }

        return $result;
    }

    /**
     * @return StoreCollection
     * @throws ArgumentException
     * @throws NotFoundException
     */
    public function getReceivers(): StoreCollection
    {
        $result = new StoreCollection();
        /** @var ScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            if (isset($result[$item->getReceiverCode()])) {
                continue;
            }

            $result[$item->getReceiverCode()] = $item->getReceiver();
        }

        return $result;
    }

    /**
     * @param Store $sender
     *
     * @return ScheduleResultCollection
     */
    public function filterBySender(Store $sender): ScheduleResultCollection
    {
        return $this->filter(function (ScheduleResult $item) use ($sender) {
            return $item->getSenderCode() === $sender->getXmlId();
        });
    }

    /**
     * @param Store $receiver
     *
     * @return ScheduleResultCollection
     */
    public function filterByReceiver(Store $receiver): ScheduleResultCollection
    {
        return $this->filter(function (ScheduleResult $item) use ($receiver) {
            return $item->getReceiverCode() === $receiver->getXmlId();
        });
    }
}
