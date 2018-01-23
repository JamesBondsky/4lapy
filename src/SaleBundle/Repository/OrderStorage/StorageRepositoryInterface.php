<?php

namespace FourPaws\SaleBundle\Repository\OrderStorage;

use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderService;

interface StorageRepositoryInterface
{
    /**
     * @param int $fuserId
     *
     * @return OrderStorage
     */
    public function findByFuser(int $fuserId): OrderStorage;

    /**
     * @param OrderStorage $storage
     *
     * @return bool
     */
    public function save(OrderStorage $storage, string $step = OrderService::AUTH_STEP): bool;

    /**
     * @param OrderStorage $storage
     *
     * @return bool
     */
    public function clear(OrderStorage $storage): bool;
}
