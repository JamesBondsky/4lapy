<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

class DostavistaDeliveryResult extends BaseResult
{
    protected $freePriceFrom;

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
}
