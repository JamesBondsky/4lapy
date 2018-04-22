<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Entity;

use FourPaws\Catalog\Model\Offer;
use FourPaws\StoreBundle\Entity\ScheduleResult;

/**
 * Class TmpDeliveryScheduleResult
 */
class DeliveryScheduleResult
{
    /**
     * @var int
     */
    protected $amount;

    /**
     * @var ScheduleResult
     */
    protected $scheduleResult;

    /**
     * @var Offer
     */
    protected $offer;

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

    /**
     * @return ScheduleResult
     */
    public function getScheduleResult(): ScheduleResult
    {
        return $this->scheduleResult;
    }

    /**
     * @param ScheduleResult $scheduleResult
     *
     * @return DeliveryScheduleResult
     */
    public function setScheduleResult(ScheduleResult $scheduleResult): DeliveryScheduleResult
    {
        $this->scheduleResult = $scheduleResult;
        return $this;
    }
}
