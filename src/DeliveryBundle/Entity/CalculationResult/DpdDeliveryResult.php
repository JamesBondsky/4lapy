<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

class DpdDeliveryResult extends BaseResult implements DeliveryResultInterface
{
    use DeliveryResultTrait;

    /**
     * Данные по длительности доставки, пришедшие от DPD
     * @var int
     */
    protected $initialPeriod = 0;

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws SystemException
     */
    public function getDeliveryDate(): \DateTime
    {
        $date = parent::getDeliveryDate();
        $modifier = $this->getInitialPeriod();

        /**
         * дата доставки DPD для зоны 4 рассчитывается как "то, что вернуло DPD" + 1 день
         */
        if ($this->getDeliveryZone() === DeliveryService::ZONE_4) {
            $modifier++;
        }

        $modifier += $this->getDateOffset();

        if ($modifier > 0) {
            $date = (clone $date)->modify(sprintf('+%s days', $modifier));
        }

        return $date;
    }

    /**
     * @return int
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @throws StoreNotFoundException
     * @throws SystemException
     */
    public function getPeriodTo(): int
    {
        return $this->getPeriodFrom() + 10;
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
     * @return DpdDeliveryResult
     */
    public function setInitialPeriod(int $initialPeriod): DpdDeliveryResult
    {
        $this->initialPeriod = $initialPeriod;
        return $this;
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
}
