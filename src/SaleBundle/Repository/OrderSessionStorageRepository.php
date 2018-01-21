<?php

namespace FourPaws\SaleBundle\Repository;

use Bitrix\Sale\Fuser;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderStorageValidationException;
use FourPaws\SaleBundle\Service\OrderService;

class OrderSessionStorageRepository extends OrderStorageBaseRepository
{
    const SESSION_KEY = 'ORDER';

    public function findByFuser(int $fuserId): OrderStorage
    {
        if (!$this->checkFuserId($fuserId)) {
            throw new NotFoundException('Wrong fuser id');
        }

        $data = $_SESSION[self::SESSION_KEY] ?? [];
        // @todo Implement findByFuser() method.
    }

    public function save(OrderStorage $storage, string $step = OrderService::AUTH_STEP): bool
    {
        if (!$this->checkFuserId($storage->getFuserId())) {
            throw new NotFoundException('Wrong fuser id');
        }

        $validationResult = $this->validator->validate($storage, null, [$step]);
        if ($validationResult->count() > 0) {
            throw new OrderStorageValidationException($validationResult, 'Wrong entity passed to create');
        }

        // @todo Implement save() method.
    }

    public function clear(OrderStorage $storage): bool
    {
        // @todo Implement clear() method.
    }

    protected function checkFuserId($fuserId): bool
    {
        return $fuserId === Fuser::getId();
    }
}
