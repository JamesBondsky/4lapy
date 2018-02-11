<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Repository\OrderStorage;

use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderStorageService;

class DatabaseStorageRepository // extends StorageBaseRepository
{
    public function save(OrderStorage $storage, string $step = OrderStorageService::AUTH_STEP): bool
    {
        // TODO: Implement save() method.
    }

    public function clear(OrderStorage $storage): bool
    {
        // TODO: Implement clear() method.
    }

    public function findByFuser(int $fuserId): OrderStorage
    {
    }
}
