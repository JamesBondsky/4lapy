<?php

namespace FourPaws\DeliveryBundle\Service;

interface DeliveryServiceInterface
{
    const ZONE_1 = 'ZONE_1';

    const ZONE_2 = 'ZONE_2';

    const ZONE_3 = 'ZONE_3';

    const ZONE_4 = 'ZONE_4';

    /**
     * Получение зоны доставки по местоположению
     *
     * @param $locationCode
     *
     * @return mixed
     */
    public function getDeliveryZone(string $locationCode = null): string;
}
