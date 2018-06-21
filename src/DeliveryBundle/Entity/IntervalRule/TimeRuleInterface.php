<?php

namespace FourPaws\DeliveryBundle\Entity\IntervalRule;

use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;

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

    /**
     * @param \DateTime                  $date
     * @param CalculationResultInterface $delivery
     * @return bool
     */
    public function isSuitable(\DateTime $date, CalculationResultInterface $delivery): bool;

    /**
     * @param \DateTime $date
     * @return \DateTime
     */
    public function apply(\DateTime $date): \DateTime;
}
