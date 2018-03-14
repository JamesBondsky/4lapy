<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

class DpdResult extends BaseResult
{
    /**
     * Данные по длительности доставки, пришедшие от DPD
     * @var int
     */
    protected $initialPeriod = 0;

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     */
    public function doCalculateDeliveryDate(): void
    {
        parent::doCalculateDeliveryDate();
        $modifier = $this->getInitialPeriod();

        /**
         * дата доставки DPD для зоны 4 рассчитывается как "то, что вернуло DPD" + 1 день
         */
        if ($this->getDeliveryCode() === DeliveryService::DPD_DELIVERY_CODE &&
            $this->getDeliveryZone() === DeliveryService::ZONE_4
        ) {
            $modifier++;
        }

        if ($modifier > 0) {
            $this->deliveryDate->modify(sprintf('+%s days', $modifier));
        }
    }

    /**
     * @return int
     * @throws NotFoundException
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @throws StoreNotFoundException
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
     * @return DpdResult
     */
    public function setInitialPeriod(int $initialPeriod): DpdResult
    {
        $this->initialPeriod = $initialPeriod;
        return $this;
    }
}
