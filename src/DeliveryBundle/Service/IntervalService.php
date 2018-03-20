<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Service;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Collection\IntervalRuleCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Entity\IntervalRule\AddDaysRule;
use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class IntervalService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const DELIVERY_INTERVALS = [
        '1' => '09:00-18:00',
        '2' => '18:00-24:00',
        '3' => '08:00-12:00',
        '4' => '12:00-16:00',
        '5' => '16:00-20:00',
        '6' => '20:00-24:00',
        '7' => '15:00-21:00',
    ];

    /**
     * IntervalService constructor.
     */
    public function __construct()
    {
        $this->setLogger(LoggerFactory::create('IntervalService'));
    }

    /**
     * @param string $type
     * @param array $data
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
     * @return IntervalRuleCollection
     */
    public function createRules(string $type, array $data): IntervalRuleCollection
    {
        $result = new IntervalRuleCollection();
        foreach ($data as $item) {
            try {
                $result->add($this->createRule($type, $item));
            } catch (NotFoundException $e) {
                $this->logger->error(sprintf('Unknown rule type %s', $type));
            }
        }

        return $result;
    }

    /**
     * @param CalculationResultInterface $delivery
     * @param IntervalCollection $intervals
     * @return Interval
     * @throws NotFoundException
     */
    public function getFirstInterval(CalculationResultInterface $delivery, IntervalCollection $intervals): Interval
    {
        $result = null;

        $min = null;
        /** @var Interval $interval */
        foreach ($intervals as $i => $interval) {
            $tmpDelivery = clone $delivery;
            $tmpDelivery->setSelectedInterval($interval);

            if ((null === $min) || $min > $tmpDelivery->getDeliveryDate()->getTimestamp()) {
                $result = $interval;
            }
        }

        if (!$result instanceof Interval) {
            throw new NotFoundException('Не найдено подходящих интервалов');
        }

        return $result;
    }

    /**
     * @param CalculationResultInterface $delivery
     * @param int $dateIndex
     *
     * @return IntervalCollection
     */
    public function getIntervalsByDate(CalculationResultInterface $delivery, int $dateIndex): IntervalCollection
    {
        $result = new IntervalCollection();

        if ($dateIndex > (int)$delivery->getPeriodTo() - 1) {
            return $result;
        }

        if (!$delivery->getStockResult()->getDelayed()->isEmpty()) {
            return $result;
        }

        $deliveryDate = clone $delivery->getDeliveryDate();
        if ($dateIndex > 0) {
            $deliveryDate->modify(sprintf('+%s days', $dateIndex));
        }

        /** @var Interval $interval */
        foreach ($delivery->getIntervals() as $interval) {
            $tmpDelivery = clone $delivery;
            $tmpDelivery->setSelectedInterval($interval);

            if ($tmpDelivery->getDeliveryDate() <= $deliveryDate) {
                $result->add($interval);
            }
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
