<?php

namespace FourPaws\StoreBundle\Service;

use FourPaws\StoreBundle\Collection\DeliveryScheduleCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
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

    /**
     * @param Store $receiver
     * @param StoreCollection|null $senders
     * @return DeliveryScheduleCollection
     * @throws NotFoundException
     */
    public function findByReceiver(Store $receiver, StoreCollection $senders = null): DeliveryScheduleCollection
    {
        return $this->repository->findByReceiver($receiver, $senders);
    }
}
