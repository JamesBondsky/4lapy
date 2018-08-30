<?php

namespace FourPaws\DeliveryBundle\Restrictions;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Bitrix\Sale\Delivery\Restrictions;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Shipment;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;

class LocationExceptRestriction extends Restrictions\Base
{
    public static function getClassTitle()
    {
        return 'все местоположения, кроме';
    }

    public static function getClassDescription()
    {
        return 'доставка будет доступна для всех местоположений, кроме заданных групп';
    }

    public static function check($locationCode, array $restrictionParams)
    {
        if (!$locationCode) {
            return false;
        }

        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        $deliveryZone = $deliveryService->getDeliveryZoneByLocation($locationCode);
        foreach ($restrictionParams as $zone => $value) {
            if ($deliveryZone !== $zone) {
                continue;
            }

            if ($value === BitrixUtils::BX_BOOL_TRUE) {
                return false;
            }
        }

        return true;
    }

    protected static function extractParams(Entity $shipment)
    {
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        if ($shipment instanceof Shipment) {
            return $deliveryService->getDeliveryLocation($shipment);
        }

        return '';
    }

    public static function getParamsStructure($entityId = 0)
    {
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        $allZones = $deliveryService->getAllZones(false);

        $result = [];
        foreach ($allZones as $code => $zone) {
            $result[$code] = [
                'TYPE'  => 'Y/N',
                'VALUE' => 'N',
                'LABEL' => 'Исключить зону ' . $zone['NAME'],
            ];
        }

        return $result;
    }
}
