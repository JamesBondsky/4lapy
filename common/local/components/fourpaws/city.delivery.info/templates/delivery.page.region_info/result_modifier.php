<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;

$deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
$deliveryResults = [];
foreach ($deliveryService->getAllZones(true) as $zoneInfo) {
    if (!$locationCode = current($zoneInfo['LOCATIONS'])) {
        continue;
    }

    if (!\in_array($zoneInfo['CODE'], [
        DeliveryService::ZONE_1,
        DeliveryService::ZONE_5,
        DeliveryService::ZONE_6,
    ], true)) {
        continue;
    }

    $deliveries = $deliveryService->getByLocation($locationCode, [DeliveryService::INNER_DELIVERY_CODE]);
    foreach ($deliveries as $delivery) {
        if ($deliveryService->isInnerDelivery($delivery)) {
            $deliveryResults[$zoneInfo['CODE']] = $delivery;
            break;
        }
    }
}

$arResult['RESULTS_BY_ZONE'] = $deliveryResults;
