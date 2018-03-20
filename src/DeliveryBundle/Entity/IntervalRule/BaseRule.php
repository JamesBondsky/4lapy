<?php

namespace FourPaws\DeliveryBundle\Entity\IntervalRule;

use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;

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
     * @param CalculationResultInterface $result
     *
     * @return bool
     */
    abstract public function isSuitable(CalculationResultInterface $result): bool;

    /**
     * @param CalculationResultInterface $result
     *
     * @return CalculationResultInterface
     */
    abstract public function apply(CalculationResultInterface $result): CalculationResultInterface;
}
