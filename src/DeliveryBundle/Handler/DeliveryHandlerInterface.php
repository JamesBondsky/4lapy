<?php

namespace FourPaws\DeliveryBundle\Handler;

use Bitrix\Sale\Shipment;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;

interface DeliveryHandlerInterface
{
    /**
     * @param Shipment $shipment
     *
     * @return IntervalCollection
     */
    public function getIntervals(Shipment $shipment): IntervalCollection;
}
