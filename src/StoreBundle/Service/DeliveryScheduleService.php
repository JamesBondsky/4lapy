<?php

namespace FourPaws\StoreBundle\Service;

use FourPaws\StoreBundle\Repository\DeliveryScheduleRepository;

class DeliveryScheduleService
{
    /**
     * @var DeliveryScheduleRepository
     */
    protected $repository;

    public function __construct(DeliveryScheduleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getByReceiver(Store $store)
    {
        $this->repository->findByReceiver($store->getXmlId());
    }
}
