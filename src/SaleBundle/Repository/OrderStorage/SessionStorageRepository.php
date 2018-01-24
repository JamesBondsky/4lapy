<?php

namespace FourPaws\SaleBundle\Repository\OrderStorage;

use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderStorageValidationException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;

class SessionStorageRepository extends StorageBaseRepository
{
    const SESSION_KEY = 'ORDER';

    public function findByFuser(int $fuserId): OrderStorage
    {
        if (!$this->checkFuserId($fuserId)) {
            throw new NotFoundException('Wrong fuser id');
        }

        $data = $_SESSION[self::SESSION_KEY];
        $data['FUSER_ID'] = $this->currentUserProvider->getCurrentFUserId();
        if (!$data['PROPERTY_COM_WAY']) {
            $data['PROPERTY_COM_WAY'] = OrderService::COMMUNICATION_SMS;
        }

        try {
            $user = $this->currentUserProvider->getCurrentUser();
            if (!$data['NAME']) {
                $data['NAME'] = $user->getName();
            }
            if (!$data['PHONE']) {
                $data['PHONE'] = $user->getPersonalPhone();
            }
            if (!$data['EMAIL']) {
                $data['EMAIL'] = $user->getEmail();
            }
        } catch (NotAuthorizedException $e) {
        }

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
        return $fuserId === $this->currentUserProvider->getCurrentFUserId();
    }
}
