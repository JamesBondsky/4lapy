<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\DeliveryBundle\Collection\IntervalRuleCollection;
use FourPaws\DeliveryBundle\Entity\IntervalRuleAddDays;
use FourPaws\DeliveryBundle\Entity\IntervalRuleBase;
use FourPaws\DeliveryBundle\Exception\NotFoundException;

class IntervalService
{
    public function applyRules(CalculationResult $result, IntervalRuleCollection $rules)
    {
        $result = clone $result;

        /* @todo apply rules */

        return $result;
    }

    /**
     * @param string $type
     * @param array $data
     *
     * @return IntervalRuleBase
     * @throws NotFoundException
     */
    public function createRule(string $type, array $data): IntervalRuleBase
    {
        switch ($type) {
            case IntervalRuleBase::TYPE_ADD_DAYS:
                return (new IntervalRuleAddDays())->setTo($data['TO'] ?? 0)
                                                  ->setFrom($data['FROM'] ?? 0)
                                                  ->setValue($data['VALUE'] ?? 0);
        }

        throw new NotFoundException(sprintf('Rule type %s not found', $type));
    }

    /**
     * @param string $type
     * @param array $data
     *
     * @return IntervalRuleCollection
     */
    public function createRules(string $type, array $data): IntervalRuleCollection
    {
        $result = new IntervalRuleCollection();
        foreach ($data as $item) {
            $result->add($this->createRule($type, $item));
        }

        return $result;
    }
}
