<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\IntervalService;

trait DeliveryResultTrait
{
    /**
     * @var IntervalCollection
     */
    protected $intervals;

    /**
     * @var Interval
     */
    protected $selectedInterval;

    /**
     * @var int
     */
    protected $dateOffset = 0;

    /**
     * @var int
     */
    protected $intervalOffset;

    /**
     * @var array
     */
    protected $weekDays;

    /**
     * @throws ApplicationCreateException
     * @return Interval|null
     */
    public function getSelectedInterval(): ?Interval
    {
        if (null === $this->selectedInterval) {
            /**
             * Если интервал не выбран, подбираем наиболее подходящий (с минимальной датой доставки)
             */
            try {
                $this->selectedInterval = $this->getFirstInterval();
            } catch (NotFoundException $e) {}
        }

        return $this->selectedInterval;
    }

    /**
     * @param Interval $selectedInterval
     *
     * @return DeliveryResultInterface
     */
    public function setSelectedInterval(Interval $selectedInterval): DeliveryResultInterface
    {
        $this->selectedInterval = $selectedInterval;
        $this->intervalOffset = null;

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this;
    }

    /**
     * @return IntervalCollection
     */
    public function getIntervals(): IntervalCollection
    {
        if (!$this->intervals) {
            $this->intervals = new IntervalCollection();
        }

        return $this->intervals;
    }

    /**
     * @param IntervalCollection $intervals
     *
     * @return DeliveryResultInterface
     */
    public function setIntervals(IntervalCollection $intervals): DeliveryResultInterface
    {
        $this->intervals = $intervals;

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this;
    }

    /**
     * @return int
     */
    public function getDateOffset(): int
    {
        return $this->dateOffset;
    }

    /**
     * @param int $dateOffset
     * @return DeliveryResultInterface
     */
    public function setDateOffset(int $dateOffset): DeliveryResultInterface
    {
        $this->dateOffset = $dateOffset;

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this;
    }

    /**
     * @return int[]
     */
    public function getWeekDays(): array
    {
        return $this->weekDays ?? [];
    }

    /**
     * @param int[] $weekDays
     * @return DeliveryResultInterface
     */
    public function setWeekDays(array $weekDays): DeliveryResultInterface
    {
        $this->weekDays = $weekDays;

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this;
    }

    /**
     * @return IntervalCollection
     */
    public function getAvailableIntervals(): IntervalCollection
    {
        $result = new IntervalCollection();

        /** @var \DateTime $deliveryDate */
        $deliveryDate = $this->getDeliveryDate();
        $days = $deliveryDate->diff($this->deliveryDate)->days;
        $tmpDelivery = clone $this;
        /** @var Interval $interval */
        foreach ($this->getIntervals() as $interval) {
            $intervalDays = $tmpDelivery->setSelectedInterval($interval)->getIntervalOffset();

            if ($intervalDays <= $days) {
                $result->add($interval);
            }
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getIntervalOffset(): int
    {
        return 0;
    }

    /**
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @return Interval
     */
    protected function getFirstInterval(): Interval
    {
        /** @var IntervalService $intervalService */
        $intervalService = Application::getInstance()->getContainer()->get(IntervalService::class);
        return $intervalService->getFirstInterval($this);
    }

    /**
     * @return int
     *
     * @throws ApplicationCreateException
     * @throws NotFoundException
     */
    protected function getFullOffset(): int
    {
        $intervalOffset = (clone $this)->setSelectedInterval($this->getFirstInterval())->getIntervalOffset();

        return max($intervalOffset, $this->getDateOffset());
    }

    /**
     * @param \DateTime $date
     *
     * @return \DateTime
     */
    protected function getNextDeliveryDate(\DateTime $date): \DateTime
    {
        $date = clone $date;
        if ($availableDays = $this->getWeekDays()) {
            $deliveryDay = (int)$date->format('N');
            while (!\in_array($deliveryDay, $availableDays, true)) {
                $deliveryDay = (int)$date->modify('+1 day')->format('N');
            }
        }

        return $date;
    }
}
