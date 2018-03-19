<?php

namespace FourPaws\DeliveryBundle\Entity\IntervalRule;

interface TimeRuleInterface
{
    /**
     * @return int
     */
    public function getTo(): int;

    /**
     * @return int
     */
    public function getFrom(): int;

    /**
     * @return int
     */
    public function getValue(): int;
}