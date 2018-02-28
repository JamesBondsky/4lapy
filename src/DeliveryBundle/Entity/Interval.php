<?php

namespace FourPaws\DeliveryBundle\Entity;

use FourPaws\DeliveryBundle\Collection\IntervalRuleCollection;

class Interval
{
    /**
     * @var int
     */
    protected $from;

    /**
     * @var int
     */
    protected $to;

    /**
     * @var IntervalRuleCollection
     */
    protected $rules;

    /**
     * @return int
     */
    public function getFrom(): int
    {
        return $this->from ?? 0;
    }

    /**
     * @param int $from
     *
     * @return Interval
     */
    public function setFrom(int $from): Interval
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return int
     */
    public function getTo(): int
    {
        return $this->to ?? 0;
    }

    /**
     * @param int $to
     *
     * @return Interval
     */
    public function setTo(int $to): Interval
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return IntervalRuleCollection
     */
    public function getRules(): IntervalRuleCollection
    {
        if (null === $this->rules) {
            $this->rules = new IntervalRuleCollection();
        }

        return $this->rules;
    }

    /**
     * @param IntervalRuleCollection $rules
     *
     * @return Interval
     */
    public function setRules(IntervalRuleCollection $rules): Interval
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return date('H:00', mktime($this->getFrom())) .
            ' - ' . date('H:00', mktime($this->getTo()));
    }
}
