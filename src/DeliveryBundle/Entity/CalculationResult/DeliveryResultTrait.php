<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\IntervalService;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

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
     * @var Interval
     */
    protected $firstInterval;

    /**
     * @var int
     */
    protected $dateOffset = 0;

    /**
     * @var int
     */
    protected $intervalOffset;

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
     *
     * @return DeliveryResultInterface
     */
    public function setDateOffset(int $dateOffset): DeliveryResultInterface
    {
        $this->dateOffset = $dateOffset;

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this;
    }

    /**
     * @param int|null $dateIndex
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @return IntervalCollection
     */
    public function getAvailableIntervals(?int $dateIndex = null): IntervalCollection
    {
        $result = new IntervalCollection();

        if (null === $dateIndex) {
            $dateIndex = $this->getDateOffset();
        }

        $diff = abs($this->getPeriodTo() - $this->getPeriodFrom());
        if (($dateIndex >= 0) && ($dateIndex <= $diff)) {
            $days = $this->getIntervalOffset() + $dateIndex;
            $tmpDelivery = clone $this;
            /** @var Interval $interval */
            foreach ($this->getIntervals() as $interval) {
                $intervalDays = $tmpDelivery->setSelectedInterval($interval)->getIntervalOffset();

                if ($intervalDays <= $days) {
                    $result->add($interval);
                }
            }
        }

        return $result;
    }

    /**
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @return Interval
     */
    protected function getFirstInterval(): Interval
    {
        if (null === $this->firstInterval) {
            /** @var IntervalService $intervalService */
            $intervalService = Application::getInstance()->getContainer()->get(IntervalService::class);
            $this->firstInterval = $intervalService->getFirstInterval($this);
        }

        return $this->firstInterval;
    }
}
