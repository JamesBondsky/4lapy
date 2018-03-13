<?php

namespace FourPaws\StoreBundle\Collection;

use FourPaws\StoreBundle\Entity\DeliverySchedule\DeliveryScheduleBase;

class DeliveryScheduleCollection extends BaseCollection
{
    /**
     * Получение ближайшего графика поставок для указанной даты
     *
     * @param \DateTime $from
     * @return DeliveryScheduleBase
     */
    public function getNextDelivery(\DateTime $from = null): ?DeliveryScheduleBase
    {
        $result = null;
        $minDate = null;

        /** @var DeliveryScheduleBase $item */
        foreach ($this->getIterator() as $item) {
            $date = $item->getNextDelivery($from);

            if ((null === $minDate) || ($minDate > $date)) {
                $minDate = $date;
                $result = $item;
            }
        }

        return $result;
    }
}
