<?php

namespace FourPaws\SaleBundle\Repository;

use Bitrix\Sale\Fuser;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderStorageValidationException;
use FourPaws\SaleBundle\Service\OrderService;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;

class OrderSessionStorageRepository extends OrderStorageBaseRepository
{
    const SESSION_KEY = 'ORDER';

    public function findByFuser(int $fuserId): OrderStorage
    {
        if (!$this->checkFuserId($fuserId)) {
            throw new NotFoundException('Wrong fuser id');
        }

        $data = $_SESSION[self::SESSION_KEY] ?? [];

        return $this->arrayTransformer->fromArray(
            $data,
            OrderStorage::class,
            DeserializationContext::create()->setGroups(['read'])
        );
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

        $_SESSION[self::SESSION_KEY] = $this->arrayTransformer->toArray(
            $storage,
            SerializationContext::create()->setGroups(
                ['update']
            )
        );

        return true;
    }

    public function clear(OrderStorage $storage): bool
    {
        unset($_SESSION[self::SESSION_KEY]);

        return true;
    }

    protected function checkFuserId($fuserId): bool
    {
        return $fuserId === Fuser::getId();
    }
}
