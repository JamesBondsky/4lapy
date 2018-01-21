<?php

namespace FourPaws\SaleBundle\Repository;

use FourPaws\SaleBundle\Entity\OrderStorage;

interface OrderStorageRepositoryInterface
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
    public function save(OrderStorage $storage): bool;

    /**
     * @param OrderStorage $storage
     *
     * @return bool
     */
    public function clear(OrderStorage $storage): bool;
}
