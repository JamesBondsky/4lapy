<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Entity\DeliverySchedule;

use DateTime;
use JMS\Serializer\Annotation as Serializer;

class DeliveryScheduleManual extends DeliveryScheduleBase
{
    /**
     * @var string
     * @Serializer\SerializedName("UF_TYPE")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","delete"})
     */
    protected $type = self::TYPE_MANUAL;

    /**
     * @param DateTime $from
     * @return DateTime|null
     */
    protected function doGetNextDelivery(DateTime $from): ?DateTime
    {
        $result = null;
        $results = [];
        $date = (clone $from)->setTime(0, 0, 0, 0);

        /** @var \DateTime $deliveryDate */
        foreach ($this->deliveryDates as $deliveryDate) {
            if (!$deliveryDate instanceof DateTime) {
                continue;
            }

            if ($deliveryDate > $date) {
                $results[] = $deliveryDate;
            }
        }

        if (!empty($results)) {
            $result = min($results);
        }

        return $result;
    }
}