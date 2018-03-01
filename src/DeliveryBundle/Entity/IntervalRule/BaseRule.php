<?php

namespace FourPaws\DeliveryBundle\Entity\IntervalRule;

use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;

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
     * @param BaseResult $result
     *
     * @return bool
     */
    abstract public function isSuitable(BaseResult $result): bool;

    /**
     * @param BaseResult $result
     *
     * @return BaseResult
     */
    abstract public function apply(BaseResult $result): BaseResult;
}
