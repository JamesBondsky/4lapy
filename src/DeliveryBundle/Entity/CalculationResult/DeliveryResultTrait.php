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
     * @var int
     */
    protected $dateOffset = 0;

    /**
     * @var int
     */
    protected $intervalOffset;

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @return Interval|null
     */
    public function getSelectedInterval(): ?Interval
    {
        $result = $this->selectedInterval;

        if (null === $result) {
            /**
             * Если интервал не выбран, подбираем наиболее подходящий (с минимальной датой доставки)
             */
            $result = $this->getFirstInterval();
        }

        return $result;
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
     * @throws NotFoundException
     * @return IntervalCollection
     */
    public function getAvailableIntervals(?int $dateIndex = null): IntervalCollection
    {
        $result = new IntervalCollection();

        if (null === $dateIndex) {
            $dateIndex = $this->getDateOffset();
        }

        /** @var \DateTime $date */
        $date = clone $this->getDeliveryDate();
        $diff = abs($this->getPeriodTo() - $this->getPeriodFrom());
        if (($dateIndex >= 0) && ($dateIndex <= $diff)) {
            if ($dateIndex > 0) {
                $date->modify(sprintf('+%s days', $dateIndex));
            }
            $date->setTime(0, 0, 0, 0);

            /** @var Interval $interval */
            foreach ($this->getIntervals() as $interval) {
                $tmpDelivery = clone $this;
                /** @var \DateTime $tmpDate */
                $tmpDate = clone $tmpDelivery->setSelectedInterval($interval)->getDeliveryDate();
                $tmpDate->setTime(0, 0, 0, 0);
                if ($tmpDate <= $date) {
                    $result->add($interval);
                }
            }
        }

        return $result;
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @return Interval|null
     */
    protected function getFirstInterval(): ?Interval
    {
        $result = null;
        /** @var IntervalService $intervalService */
        $intervalService = Application::getInstance()->getContainer()->get(IntervalService::class);
        try {
            $result = $intervalService->getFirstInterval(
                $this->getIntervals()
            );
        } catch (NotFoundException $e) {
            if (!$this->getIntervals()->isEmpty()) {
                $result = $this->getIntervals()->first();
            }
        }

        return $result;
    }
}
