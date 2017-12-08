<?php

namespace FourPaws\DeliveryBundle\Service;

interface DeliveryServiceInterface
{
    const ZONE_1 = '1';

    const ZONE_2 = '2';

    const ZONE_3 = '3';

    const ZONE_4 = '4';

    /**
     * Получение зоны доставки по местоположению
     *
     * @param $locationCode
     *
     * @return mixed
     */
    public function getDeliveryZone(string $locationCode = null): string;
}
