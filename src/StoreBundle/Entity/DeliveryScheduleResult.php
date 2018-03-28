<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Entity;

/**
 * Class DeliveryScheduleResult
 */
class DeliveryScheduleResult
{
    /** @var \DateTime */
    protected $date;

    /** @var DeliverySchedule */
    protected $schedule;

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return DeliveryScheduleResult
     */
    public function setDate(\DateTime $date): DeliveryScheduleResult
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return DeliverySchedule
     */
    public function getSchedule(): DeliverySchedule
    {
        return $this->schedule;
    }

    /**
     * @param DeliverySchedule $schedule
     * @return DeliveryScheduleResult
     */
    public function setSchedule(DeliverySchedule $schedule): DeliveryScheduleResult
    {
        $this->schedule = $schedule;
        return $this;
    }
}
