<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;


use Bitrix\Main\ArgumentException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

class DpdPickupResult extends DpdResult implements PickupResultInterface
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
     * @return Store
     * @throws NotFoundException
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
     * @return DpdResult
     */
    public function setTerminals(StoreCollection $terminals): DpdResult
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
     * @throws StoreNotFoundException
     */
    public function isSuccess($internalCall = false)
    {
        return parent::isSuccess($internalCall);
    }
}
