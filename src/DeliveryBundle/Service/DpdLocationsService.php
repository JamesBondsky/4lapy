<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Service;

use FourPaws\DeliveryBundle\Entity\DpdLocation;
use FourPaws\DeliveryBundle\Exception\LocationNotFoundException;
use FourPaws\DeliveryBundle\Repository\DpdLocationRepository;

class DpdLocationsService
{
    /** @var DpdLocationRepository */
    protected $repository;

    public function __construct(DpdLocationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int $id
     *
     * @throws LocationNotFoundException
     * @return DpdLocation
     */
    public function getOneById(int $id): DpdLocation
    {
        $result = $this->repository->find($id);

        if (!$result instanceof DpdLocation) {
            throw new LocationNotFoundException(sprintf('Dpd location with id %s not found', $id));
        }

        return $result;
    }

    /**
     * @param string $code
     *
     * @throws LocationNotFoundException
     * @return DpdLocation
     */
    public function getOneByCode(string $code): DpdLocation
    {
        $result = $this->repository->findBy(['CITY_CODE' => $code])->first();

        if (!$result instanceof DpdLocation) {
            throw new LocationNotFoundException(sprintf('Dpd location with code %s not found', $code));
        }

        return $result;
    }

    /**
     * @param int $dpdId
     *
     * @throws LocationNotFoundException
     * @return DpdLocation
     */
    public function getOneByDpdId(int $dpdId): DpdLocation
    {
        $result = $this->repository->findBy(['CITY_CODE' => $dpdId])->first();

        if (!$result instanceof DpdLocation) {
            throw new LocationNotFoundException(sprintf('Dpd location with city id %s not found', $dpdId));
        }

        return $result;
    }

    public function getMultipleById(array $ids)
    {

    }

    public function getMultipleByDpdId(array $ids)
    {

    }

    public function getMultipleByCode(array $codes)
    {

    }
}