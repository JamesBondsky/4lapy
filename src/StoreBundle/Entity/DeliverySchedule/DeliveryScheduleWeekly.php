<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Entity\DeliverySchedule;

use DateTime;
use JMS\Serializer\Annotation as Serializer;

class DeliveryScheduleWeekly extends DeliveryScheduleBase
{
    /**
     * @var string
     * @Serializer\SerializedName("UF_TYPE")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","delete"})
     */
    protected $type = self::TYPE_WEEKLY;

    /**
     * @param DateTime $from
     * @return DateTime|null
     */
    protected function doGetNextDelivery(DateTime $from): ?DateTime
    {
        $fromDay = (int)$from->format('N');
        $results = [];
        /** @var int $day */
        foreach ($this->getDaysOfWeek() as $day) {
            $date = clone $from;
            $diff = $day - $fromDay;
            $days = ($diff >= 0) ? $diff : $diff + 7;

            $date->modify(sprintf('+%s days', $days));
            if (!$this->activeTo || $date < $this->activeTo) {
                $results[] = $date;
            }
        }

        return !empty($results) ? max($results) : null;
    }
}