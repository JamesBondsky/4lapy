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
    public const COMMUNICATION_SMS = '01';

    public const COMMUNICATION_PHONE = '02';

    public const COMMUNICATION_PHONE_ANALYSIS = '03';

    public const COMMUNICATION_ONE_CLICK = '04';

    public const COMMUNICATION_ADDRESS_ANALYSIS = '05';

    public const COMMUNICATION_PAYMENT_ANALYSIS = '06';

    public const COMMUNICATION_SUBSCRIBE = '07';

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
     * @throws \Bitrix\Main\ArgumentException
     * @return OrderPropertyVariantCollection
     */
    public function getPropertyVariants(OrderProperty $property): OrderPropertyVariantCollection
    {
        return $this->variantRepository->findByProperty($property);
    }
}
