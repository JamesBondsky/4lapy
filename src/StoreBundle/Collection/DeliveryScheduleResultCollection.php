<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Collection;


use FourPaws\StoreBundle\Entity\DeliveryScheduleResult;
use FourPaws\StoreBundle\Entity\Store;

class DeliveryScheduleResultCollection extends BaseCollection
{
    /**
     * @return DeliveryScheduleResult|null
     */
    public function getFastest(): ?DeliveryScheduleResult
    {
        /** @var DeliveryScheduleResult $result */
        $result = null;
        /** @var DeliveryScheduleResult $item */
        foreach ($this->getIterator() as $item) {
            if (null === $result || $result->getDate() > $item->getDate()) {
                $result = $item;
            }
        }

        return $result;
    }

    /**
     * @param Store $store
     * @return DeliveryScheduleResultCollection
     */
    public function findByReceiver(Store $store): DeliveryScheduleResultCollection
    {
        return $this->filter(function (DeliveryScheduleResult $result) use ($store) {
            return $result->getSchedule()->getReceiverCode() === $store->getXmlId();
        });
    }
}
