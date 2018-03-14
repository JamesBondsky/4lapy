<?php

namespace FourPaws\StoreBundle\Service;

use Bitrix\Main\ArgumentException;
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
     * Поиск графиков поставок на $receiver с фильтрацией по $senders
     *
     * @param Store $receiver
     * @param StoreCollection|null $senders
     * @return DeliveryScheduleCollection
     * @throws NotFoundException
     * @throws ArgumentException
     */
    public function findByReceiver(Store $receiver, StoreCollection $senders = null): DeliveryScheduleCollection
    {
        return $this->repository->findByReceiver($receiver, $senders, 0);
    }

    /**
     * Поиск графиков поставок на $receiver с фильтрацией по $senders с учетом промежуточных складов
     *
     * @param Store $receiver
     * @param StoreCollection|null $senders
     * @param int $maxTransitions
     * @return DeliveryScheduleCollection
     * @throws NotFoundException
     * @throws ArgumentException
     */
    public function findByReceiverWithTransitions(
        Store $receiver,
        StoreCollection $senders = null,
        $maxTransitions = 1
    ): DeliveryScheduleCollection {
        return $this->repository->findByReceiver($receiver, $senders, $maxTransitions);
    }
}
