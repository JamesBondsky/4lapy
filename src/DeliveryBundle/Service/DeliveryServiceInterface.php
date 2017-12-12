<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Sale\Shipment;

interface DeliveryServiceInterface
{
    const ZONE_1 = 'ZONE_1';

    const ZONE_2 = 'ZONE_2';

    const ZONE_3 = 'ZONE_3';

    const ZONE_4 = 'ZONE_4';

    const LOCATION_RESTRICTION_TYPE_LOCATION = 'L';

    const LOCATION_RESTRICTION_TYPE_GROUP = 'G';

    /**
     * Получение кода зоны доставки. Содержит либо код группы доставки,
     * либо код местоположения (в случае, если в ограничениях указано отдельное местоположение)
     *
     * @param Shipment $shipment
     *
     * @return bool|string
     */
    public function getDeliveryZoneCode(Shipment $shipment);

    /**
     * Получение доступных зон доставки в соответствии с ограничениями по местоположению
     *
     * @return array
     */
    public function getAvailableZones(): array;
}
