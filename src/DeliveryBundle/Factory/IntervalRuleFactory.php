<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Factory;

use FourPaws\App\Tools\StaticLoggerTrait;
use FourPaws\DeliveryBundle\Collection\IntervalRuleCollection;
use FourPaws\DeliveryBundle\Entity\IntervalRule\AddDaysRule;
use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;
use FourPaws\DeliveryBundle\Exception\IntervalRuleTypeNotFoundException;

class IntervalRuleFactory
{
    use StaticLoggerTrait;

    /**
     * @param string $type
     * @param array $data
     * @throws IntervalRuleTypeNotFoundException
     * @return BaseRule
     */
    public static function createRule(string $type, array $data): BaseRule
    {
        switch ($type) {
            case BaseRule::TYPE_ADD_DAYS:
                return (new AddDaysRule())
                    ->setTo($data['TO'] ?? 0)
                    ->setFrom($data['FROM'] ?? 0)
                    ->setValue($data['VALUE'] ?? 0);
        }

        throw new IntervalRuleTypeNotFoundException(
            \sprintf('Rule type %s not found', $type)
        );
    }

    /**
     * @param string $type
     * @param array $data
     *
     * @return IntervalRuleCollection
     */
    public static function createRules(string $type, array $data): IntervalRuleCollection
    {
        $result = new IntervalRuleCollection();
        foreach ($data as $item) {
            try {
                $result->add(static::createRule($type, $item));
            } catch (IntervalRuleTypeNotFoundException $e) {
                static::getLogger()->error('Unknown interval rule type', ['type' => $type, 'data' => $data]);
            }
        }

        return $result;
    }
}