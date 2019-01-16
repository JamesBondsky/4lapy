<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

class DostavistaDeliveryResult extends BaseResult implements DeliveryResultInterface
{
    protected $freePriceFrom;
    protected $intervals;
    protected $offset;
    protected $days;
    protected $selectedInterval;

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws SystemException
     */
    public function getDeliveryDate(): \DateTime
    {
        $date = parent::getDeliveryDate();

        return $date;
    }

    /**
     * @param Store $selectedStore
     * @return CalculationResultInterface
     */
    public function setSelectedStore(Store $selectedStore): CalculationResultInterface
    {
        $this->selectedStore = $selectedStore;
        return $this;
    }

    /**
     * @param bool $internalCall
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws SystemException
     */
    public function isSuccess($internalCall = false)
    {
        return parent::isSuccess($internalCall);
    }

    /**
     * @param Offer $offer
     *
     * @return bool
     * @throws ApplicationCreateException
     */
    protected function checkIsDeliverable(Offer $offer): bool
    {
        return parent::checkIsDeliverable($offer) && $offer->getProduct()->isDeliveryAvailable();
    }

    /**
     * @param $freePriceFrom
     * @return DostavistaDeliveryResult
     */
    protected function setFreePriceFrom($freePriceFrom)
    {
        $this->freePriceFrom = $freePriceFrom;

        return $this;
    }

    /**
     * @return DostavistaDeliveryResult
     */
    protected function getFreePriceFrom()
    {
        return $this->freePriceFrom;
    }

    /**
     * @return IntervalCollection
     */
    public function getIntervals(): IntervalCollection
    {
        return new IntervalCollection();
    }

    /**
     * @param IntervalCollection $intervals
     *
     * @return DeliveryResultInterface
     */
    public function setIntervals(IntervalCollection $intervals): DeliveryResultInterface
    {
        $this->intervals = $intervals;

        return $this;
    }

    /**
     * @return int
     */
    public function getDateOffset(): int
    {
        return 0;
    }

    /**
     * @param int $offset
     *
     * @return DeliveryResultInterface
     */
    public function setDateOffset(int $offset): DeliveryResultInterface
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getWeekDays(): array
    {
        return [];
    }

    /**
     * @param int[] $days
     *
     * @return DeliveryResultInterface
     */
    public function setWeekDays(array $days): DeliveryResultInterface
    {
        $this->days = $days;
        return $this;
    }

    /**
     * @return IntervalCollection
     */
    public function getAvailableIntervals(): IntervalCollection
    {
        return new IntervalCollection;
    }

    /**
     * @return Interval
     */
    public function getSelectedInterval(): ?Interval
    {
        return new Interval;
    }

    /**
     * @param Interval $selectedInterval
     *
     * @return DeliveryResultInterface
     */
    public function setSelectedInterval(Interval $selectedInterval): DeliveryResultInterface
    {
        $this->selectedInterval = $selectedInterval;

        return $this;
    }

    /**
     * @return int
     */
    public function getIntervalOffset(): int
    {
        return 0;
    }
}
