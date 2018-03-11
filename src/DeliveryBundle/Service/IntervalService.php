<?php

namespace FourPaws\DeliveryBundle\Service;

use FourPaws\DeliveryBundle\Collection\IntervalRuleCollection;
use FourPaws\DeliveryBundle\Entity\IntervalRule\AddDaysRule;
use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;
use FourPaws\DeliveryBundle\Exception\NotFoundException;

class IntervalService
{
    /**
     * @param string $type
     * @param array $data
     *
     * @return BaseRule
     * @throws NotFoundException
     */
    public function createRule(string $type, array $data): BaseRule
    {
        switch ($type) {
            case BaseRule::TYPE_ADD_DAYS:
                return (new AddDaysRule())->setTo($data['TO'] ?? 0)
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
