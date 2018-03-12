<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use FourPaws\DeliveryBundle\Service\DeliveryService;

class DpdResult extends BaseResult
{
    public function doCalculateDeliveryDate(): void
    {
        parent::doCalculateDeliveryDate();
        $modifier = (int)$this->getPeriodFrom();

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
}
