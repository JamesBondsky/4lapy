<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Service;

use FourPaws\DeliveryBundle\Collection\IntervalRuleCollection;
use FourPaws\DeliveryBundle\Entity\IntervalRule\AddDaysRule;
use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;
use FourPaws\DeliveryBundle\Exception\NotFoundException;

class IntervalService
{
    const DELIVERY_INTERVALS = [
        '1' => '09:00-18:00',
        '2' => '18:00-24:00',
        '3' => '08:00-12:00',
        '4' => '12:00-16:00',
        '5' => '16:00-20:00',
        '6' => '20:00-24:00',
        '7' => '15:00-21:00',
    ];

    /**
     * @param string $type
     * @param array $data
     *
     * @throws NotFoundException
     * @return BaseRule
     */
    public function createRule(string $type, array $data): BaseRule
    {
        switch ($type) {
            case BaseRule::TYPE_ADD_DAYS:
                return (new AddDaysRule())
                    ->setTo($data['TO'] ?? 0)
                    ->setFrom($data['FROM'] ?? 0)
                    ->setValue($data['VALUE'] ?? 0);
        }

        throw new NotFoundException(sprintf('Rule type %s not found', $type));
    }

    /**
     * @param string $type
     * @param array $data
     * @throws NotFoundException
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

    /**
     * @param string $interval
     * @throws NotFoundException
     * @return string
     */
    public function getIntervalCode(string $interval): string
    {
        $code = array_search($interval, static::DELIVERY_INTERVALS, true);
        if (false === $code) {
            throw new NotFoundException(sprintf('Interval %s not found', $interval));
        }

        return $code;
    }

    /**
     * @param string $code
     * @throws NotFoundException
     * @return string
     */
    public function getIntervalByCode(string $code): string
    {
        if (!isset(static::DELIVERY_INTERVALS[$code])) {
            throw new NotFoundException(sprintf('Interval with code %s not found', $code));
        }

        return static::DELIVERY_INTERVALS[$code];
    }
}
