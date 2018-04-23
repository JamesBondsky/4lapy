<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

class PickupResult extends BaseResult implements PickupResultInterface
{
    /** @var StoreCollection */
    protected $bestShops;

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     */
    protected function doCalculateDeliveryDate(): void
    {
        parent::doCalculateDeliveryDate();
        if ((null !== $this->fullstockResult) && !$this->stockResult->getUnavailable()->isEmpty()) {
            $this->addError(new Error('Присутствуют товары не в наличии'));
        }
    }

    protected function doCalculatePeriod(): void
    {
        parent::doCalculatePeriod();
        $days = $this->deliveryDate->diff($this->getCurrentDate())->days;
        if ($days === 0) {
            $hours = $this->deliveryDate->diff($this->getCurrentDate())->h;
            $this->setPeriodFrom($hours >= 1 ? $hours : 1);
            $this->setPeriodType(self::PERIOD_TYPE_HOUR);
        }
    }

    /**
     * @return int
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @throws StoreNotFoundException
     */
    public function getPeriodTo(): int
    {
        return $this->getPeriodFrom();
    }

    /**
     * @return Store
     */
    public function getSelectedShop(): Store
    {
        return $this->getSelectedStore();
    }

    /**
     * @param Store $selectedStore
     *
     * @return PickupResultInterface
     */
    public function setSelectedShop(Store $selectedStore): PickupResultInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->setSelectedStore($selectedStore);
    }

    /**
     * @return StoreCollection
     */
    public function getBestShops(): StoreCollection
    {
        if (null === $this->bestShops) {
            $this->bestShops = $this->doGetBestStores();
        }

        return $this->bestShops;
    }/** @noinspection SenselessProxyMethodInspection */

    /**
     * @param bool $internalCall
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     */
    public function isSuccess($internalCall = false)
    {
        return parent::isSuccess($internalCall);
    }
}
