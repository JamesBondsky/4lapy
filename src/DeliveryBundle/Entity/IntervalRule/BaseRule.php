<?php

namespace FourPaws\DeliveryBundle\Entity\IntervalRule;

use FourPaws\DeliveryBundle\Entity\CalculationResult;

abstract class BaseRule
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

    /**
     * @param CalculationResult $result
     *
     * @return bool
     */
    abstract public function isSuitable(CalculationResult $result): bool;

    /**
     * @param CalculationResult $result
     *
     * @return CalculationResult
     */
    abstract public function apply(CalculationResult $result): CalculationResult;
}
