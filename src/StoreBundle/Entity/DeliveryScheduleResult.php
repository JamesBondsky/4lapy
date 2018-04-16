<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Entity;

use FourPaws\Catalog\Model\Offer;

/**
 * Class DeliveryScheduleResult
 */
class DeliveryScheduleResult
{
    /** @var \DateTime */
    protected $date;

    /** @var DeliverySchedule */
    protected $schedule;

    /** @var Offer */
    protected $offer;

    /** @var int */
    protected $amount;

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

    /**
     * @return Offer
     */
    public function getOffer(): Offer
    {
        return $this->offer;
    }

    /**
     * @param Offer $offer
     *
     * @return DeliveryScheduleResult
     */
    public function setOffer(Offer $offer): DeliveryScheduleResult
    {
        $this->offer = $offer;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     *
     * @return DeliveryScheduleResult
     */
    public function setAmount(int $amount): DeliveryScheduleResult
    {
        $this->amount = $amount;
        return $this;
    }
}
