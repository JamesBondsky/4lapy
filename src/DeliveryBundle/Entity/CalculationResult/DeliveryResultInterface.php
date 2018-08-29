<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Entity\Interval;

interface DeliveryResultInterface extends CalculationResultInterface
{
    /**
     * @return IntervalCollection
     */
    public function getIntervals(): IntervalCollection;

    /**
     * @param IntervalCollection $intervals
     *
     * @return DeliveryResultInterface
     */
    public function setIntervals(IntervalCollection $intervals): DeliveryResultInterface;

    /**
     * @return int[]
     */
    public function getWeekDays(): array;

    /**
     * @param int[] $days
     *
     * @return DeliveryResultInterface
     */
    public function setWeekDays(array $days): DeliveryResultInterface;

    /**
     * @param int|null $dateIndex
     *
     * @return IntervalCollection
     */
    public function getAvailableIntervals(?int $dateIndex = null): IntervalCollection;

    /**
     * @return Interval
     */
    public function getSelectedInterval(): ?Interval;

    /**
     * @param Interval $selectedInterval
     *
     * @return DeliveryResultInterface
     */
    public function setSelectedInterval(Interval $selectedInterval): DeliveryResultInterface;

    /**
     * @return int
     */
    public function getIntervalOffset(): int;
}
