<?php

namespace FourPaws\DeliveryBundle\Entity;

abstract class IntervalRuleBase
{
    const TYPE_ADD_DAYS = 'ADD_DAYS';

    /**
     * @var string
     */
    protected $type;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
