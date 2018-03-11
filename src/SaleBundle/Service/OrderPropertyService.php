<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Service;

use FourPaws\SaleBundle\Collection\OrderPropertyCollection;
use FourPaws\SaleBundle\Collection\OrderPropertyVariantCollection;
use FourPaws\SaleBundle\Entity\OrderProperty;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Repository\OrderPropertyRepository;
use FourPaws\SaleBundle\Repository\OrderPropertyVariantRepository;

class OrderPropertyService
{
    const COMMUNICATION_SMS = '01';

    const COMMUNICATION_PHONE = '02';

    const COMMUNICATION_PHONE_ANALYSIS = '03';

    /**
     * @var OrderPropertyVariantRepository
     */
    protected $variantRepository;

    /**
     * @var OrderPropertyRepository
     */
    protected $propertyRepository;

    public function __construct(
        OrderPropertyRepository $propertyRepository,
        OrderPropertyVariantRepository $variantRepository
    ) {
        $this->variantRepository = $variantRepository;
        $this->propertyRepository = $propertyRepository;
    }

    /**
     * @return OrderPropertyCollection
     */
    public function getProperties(): OrderPropertyCollection
    {
        return $this->propertyRepository->findBy();
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     * @return OrderProperty
     */
    public function getPropertyById(int $id): OrderProperty
    {
        return $this->propertyRepository->findById($id);
    }

    /**
     * @param string $code
     *
     * @throws NotFoundException
     * @return OrderProperty
     */
    public function getPropertyByCode(string $code): OrderProperty
    {
        return $this->propertyRepository->findByCode($code);
    }

    /**
     * @param OrderProperty $property
     *
     * @return OrderPropertyVariantCollection
     */
    public function getPropertyVariants(OrderProperty $property): OrderPropertyVariantCollection
    {
        return $this->variantRepository->findByProperty($property);
    }
}
