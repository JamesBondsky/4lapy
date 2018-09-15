<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
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
        '1'  => '10:00-18:00',
        '2'  => '18:00-00:00',
        '3'  => '10:00-14:00',
        '4'  => '14:00-18:00',
        '5'  => '18:00-22:00',
        '6'  => '22:00-00:00',
        '7'  => '15:00-21:00',
        '8'  => '12:00-20:00',
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
