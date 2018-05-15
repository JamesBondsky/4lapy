<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Repository;

use FourPaws\BitrixOrmBundle\Orm\D7Repository;
use FourPaws\DeliveryBundle\Entity\DpdLocation;

class DpdLocationRepository extends D7Repository
{
    /**
     * @param string $code
     * @return DpdLocation| null
     */
    public function findByCode(string $code): ?DpdLocation
    {
        return $this->findBy(['CODE' => $code])->first() ?: null;
    }

    /**
     * @param int $dpdId
     * @return DpdLocation| null
     */
    public function findByDpdId(int $dpdId): ?DpdLocation
    {
        return $this->findBy(['CITY_ID' => $dpdId])->first() ?: null;
    }
}