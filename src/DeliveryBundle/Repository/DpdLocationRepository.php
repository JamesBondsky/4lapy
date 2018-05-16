<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Repository;

use Bitrix\Main\SystemException;
use FourPaws\BitrixOrmBundle\Orm\D7EntityManager;
use FourPaws\BitrixOrmBundle\Orm\D7Repository;
use FourPaws\DeliveryBundle\Entity\DpdLocation;
use FourPaws\DeliveryBundle\Repository\Table\DpdLocationTable;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DpdLocationRepository extends D7Repository
{
    /**
     * DpdLocationRepository constructor.
     * @param ValidatorInterface        $validator
     * @param ArrayTransformerInterface $arrayTransformer
     * @throws SystemException
     */
    public function __construct(ValidatorInterface $validator, ArrayTransformerInterface $arrayTransformer)
    {
        $dataManager = DpdLocationTable::getEntity()->getDataClass();
        $entityManager = new D7EntityManager(
            DpdLocation::class,
            $validator,
            $arrayTransformer,
            new $dataManager()
        );
        parent::__construct($entityManager);
    }

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