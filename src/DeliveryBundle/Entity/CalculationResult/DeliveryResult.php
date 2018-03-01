<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;
use FourPaws\DeliveryBundle\Entity\IntervalRule\TimeRuleInterface;

class DeliveryResult extends BaseResult
{
    public function doCalculateDeliveryDate()
    {
        parent::doCalculateDeliveryDate();
        $currentDate = new \DateTime();

        if ($this->deliveryDate->format('z') !== $currentDate->format('z')) {
            return null;
        }

        if ($this->getIntervals()->isEmpty()) {
            return null;
        }

        /**
         * Расчет даты доставки с учетом правил интервалов
         */
        $interval = $this->getIntervals()->first();
        /** @var BaseRule $rule */
        foreach ($interval->getRules() as $rule) {
            if (!$rule instanceof TimeRuleInterface) {
                continue;
            }

            $rule->apply($this);
        }
    }
}
