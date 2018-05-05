<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Dpd\Services;

use Bitrix\Sale\Delivery\Services\AutomaticProfile as BitrixAutomaticProfile;
use Bitrix\Sale\Shipment;
use FourPaws\DeliveryBundle\Dpd\Calculator;

class AutomaticProfile extends BitrixAutomaticProfile
{
    protected function calculateConcrete(Shipment $shipment)
    {
        Calculator::$bitrixShipment = $shipment;
        /** @todo перенести сюда расчеты из FourPaws\DeliveryBundle\Dpd\Calculator */
        return parent::calculateConcrete($shipment);
    }
}
