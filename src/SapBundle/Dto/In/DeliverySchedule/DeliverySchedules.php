<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\DeliverySchedule;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class DeliverySchedules
 *
 * @Serializer\XmlRoot("ns0:mt_DeliverySchedule")
 * @Serializer\XmlNamespace(uri="urn:4lapy.ru:ERP_2_BITRIX:DataExchange", prefix="ns0")
 *
 * @package FourPaws\SapBundle\Dto\In\DeliverySchedule
 */
class DeliverySchedules
{
    /**
     * @Serializer\XmlList(inline=true, entry="DLVSCHEDULE")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\DeliverySchedule\DeliverySchedule>")
     *
     * @var Collection|DeliverySchedule[]
     */
    protected $schedules;

    /**
     * @return Collection|DeliverySchedule[]
     */
    public function getSchedules()
    {
        return $this->schedules;
    }

    /**
     * @param Collection|DeliverySchedule[] $schedules
     *
     * @return DeliverySchedules
     */
    public function setSchedules($schedules): DeliverySchedules
    {
        $this->schedules = $schedules;

        return $this;
    }
}
