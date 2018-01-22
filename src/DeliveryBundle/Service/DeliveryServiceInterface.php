<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Sale\Shipment;

interface DeliveryServiceInterface
{
    /**
     * Получение интервалов доставки
     *
     * @return array
     */
    public function getIntervals(Shipment $shipment): array;
}
