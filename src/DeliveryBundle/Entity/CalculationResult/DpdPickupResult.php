<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;


use Bitrix\Main\ArgumentException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;

class DpdPickupResult extends BaseResult implements PickupResultInterface
{
    /**
     * @var Store
     */
    protected $selectedPickupPoint;

    /**
     * @var StoreCollection
     */
    protected $terminals;

    /**
     * Данные по длительности доставки, пришедшие от DPD
     * @var int
     */
    protected $initialPeriod = 0;

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     */
    public function doCalculateDeliveryDate(): void
    {
        parent::doCalculateDeliveryDate();

        if ($this->getInitialPeriod() > 0) {
            $this->deliveryDate->modify(sprintf('+%s days', $this->getInitialPeriod()));
        }
    }

    /**
     * @return Store
     */
    public function getSelectedStore(): Store
    {
        if (null === $this->selectedPickupPoint) {
            $this->selectedPickupPoint = $this->getTerminals()->first();
            $this->selectedStore = $this->getStockResult()->getStores()->first();
        }

        return $this->selectedPickupPoint;
    }

    public function setSelectedStore(Store $selectedStore): CalculationResultInterface
    {
        $this->selectedPickupPoint = $selectedStore;
        return $this;
    }

    /**
     * @return StoreCollection
     */
    public function getTerminals(): StoreCollection
    {
        return $this->terminals;
    }

    /**
     * @param StoreCollection $terminals
     * @return DpdPickupResult
     */
    public function setTerminals(StoreCollection $terminals): DpdPickupResult
    {
        $this->terminals = $terminals;
        return $this;
    }

    /**
     * @return StoreCollection
     */
    public function getBestShops(): StoreCollection
    {
        return $this->getTerminals();
    }/** @noinspection SenselessProxyMethodInspection */

    /**
     * @param bool $internalCall
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     */
    public function isSuccess($internalCall = false)
    {
        return parent::isSuccess($internalCall);
    }

    /**
     * @return int
     */
    public function getInitialPeriod(): int
    {
        return $this->initialPeriod;
    }

    /**
     * @param int $initialPeriod
     * @return DpdPickupResult
     */
    public function setInitialPeriod(int $initialPeriod): DpdPickupResult
    {
        $this->resetResult();
        $this->initialPeriod = $initialPeriod;
        return $this;
    }

    /**
     * @return int
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @throws NotFoundException
     */
    public function getPeriodTo(): int
    {
        return $this->getPeriodFrom();
    }
}
