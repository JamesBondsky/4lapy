<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Entity\DeliverySchedule;

use DateTime;
use JMS\Serializer\Annotation as Serializer;

class DeliveryScheduleByWeek extends DeliveryScheduleWeekly
{
    /**
     * @var string
     * @Serializer\SerializedName("UF_TYPE")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","delete"})
     */
    protected $type = self::TYPE_BY_WEEK;

    /**
     * @param DateTime $from
     * @return DateTime|null
     */
    protected function doGetNextDelivery(DateTime $from): ?DateTime
    {
        $weekNumber = $this->getWeekNumber();
        $weekDate = clone $from;
        $weekDate->setISODate($from->format('Y'), $weekNumber);

        if ($weekDate < $from) {
            $weekDate->modify('+1 year');
        }

        return parent::doGetNextDelivery($weekDate);
    }
}