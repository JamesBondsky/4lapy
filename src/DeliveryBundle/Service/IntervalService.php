<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Collection\IntervalRuleCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Entity\IntervalRule\AddDaysRule;
use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use Psr\Log\LoggerAwareInterface;

/**
 * Class IntervalService
 *
 * @package FourPaws\DeliveryBundle\Service
 */
class IntervalService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const DELIVERY_INTERVALS = [
        '1' => '09:00-18:00',
        '2' => '18:00-00:00',
        '3' => '08:00-12:00',
        '4' => '12:00-16:00',
        '5' => '16:00-20:00',
        '6' => '20:00-00:00',
        '7' => '15:00-21:00',
    ];

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * IntervalService constructor.
     * @param DeliveryService $deliveryService
     */
    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
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

        throw new NotFoundException(
            \sprintf('Rule type %s not found', $type)
        );
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
            try {
                $result->add($this->createRule($type, $item));
            } catch (NotFoundException $e) {
                $this->logger->error('Unknown interval rule type', ['type' => $type]);
            }
        }

        return $result;
    }

    /**
     * @param DeliveryResultInterface $delivery
     * @throws NotFoundException
     * @return Interval
     */
    public function getFirstInterval(DeliveryResultInterface $delivery): Interval
    {
        $result = null;

        $intervals = $delivery->getIntervals();
        if ($delivery instanceof DeliveryResultInterface) {
            $min = null;

            $tmpDelivery = clone $delivery;
            /** @var Interval $interval */
            foreach ($intervals as $i => $interval) {
                $tmpDelivery->setSelectedInterval($interval);

                if ((null === $min) || $min > $tmpDelivery->getIntervalOffset()) {
                    $result = $interval;
                    $min = $tmpDelivery->getIntervalOffset();
                }
            }
        } else {
            $result = $intervals->first();
        }

        if (!$result instanceof Interval) {
            throw new NotFoundException('No intervals found');
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
        $code = \array_search($interval, static::DELIVERY_INTERVALS, true);

        if (false === $code) {
            throw new NotFoundException(
                \sprintf('Interval %s not found', $interval)
            );
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
        $code = trim($code, '0');

        if (!isset(static::DELIVERY_INTERVALS[$code])) {
            throw new NotFoundException(
                \sprintf('Interval with code %s not found', $code)
            );
        }

        return static::DELIVERY_INTERVALS[$code];
    }
}
