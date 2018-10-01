<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Collection;

use FourPaws\StoreBundle\Entity\ScheduleResult;
use FourPaws\StoreBundle\Entity\Store;

class ScheduleResultCollection extends BaseCollection
{
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
