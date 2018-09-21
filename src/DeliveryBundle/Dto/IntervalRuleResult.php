<?php

namespace FourPaws\DeliveryBundle\Dto;


class IntervalRuleResult
{
    /**
     * @var int
     */
    protected $timeFrom;

    /**
     * @var int
     */
    protected $timeTo;

    /**
     * @var int
     */
    protected $days;

    /**
     * @return int
     */
    public function getTimeFrom(): int
    {
        return $this->timeFrom;
    }

    /**
     * @param int $timeFrom
     * @return IntervalRuleResult
     */
    public function setTimeFrom(int $timeFrom): IntervalRuleResult
    {
        $this->timeFrom = $timeFrom;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeTo(): int
    {
        return $this->timeTo;
    }

    /**
     * @param int $timeTo
     * @return IntervalRuleResult
     */
    public function setTimeTo(int $timeTo): IntervalRuleResult
    {
        $this->timeTo = $timeTo;

        return $this;
    }

    /**
     * @return int
     */
    public function getDays(): int
    {
        return $this->days;
    }

    /**
     * @param int $days
     * @return IntervalRuleResult
     */
    public function setDays(int $days): IntervalRuleResult
    {
        $this->days = $days;

        return $this;
    }
}
