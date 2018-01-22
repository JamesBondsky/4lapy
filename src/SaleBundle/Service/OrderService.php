<?php

namespace FourPaws\SaleBundle\Service;

use Bitrix\Sale\Fuser;
use Bitrix\Sale\Order;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Repository\OrderStorageRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

class OrderService
{
    const AUTH_STEP = 'auth';

    const DELIVERY_STEP = 'delivery';

    const PAYMENT_STEP = 'payment';

    const COMPLETE_STEP = 'complete';

    /**
     * @var OrderStorageRepositoryInterface
     */
    protected $storageRepository;

    public function __construct(OrderStorageRepositoryInterface $orderStorageRepository)
    {
        $this->storageRepository = $orderStorageRepository;
    }

    /**
     * Получение заказа по id
     *
     * @param int $id id заказа
     * @param bool $check выполнять ли проверки
     * @param int $userId id пользователя, к которому привязан заказ
     * @param string $hash хеш заказа (проверяется, если не передан userId)
     *
     * @return Order
     * @throws NotFoundException
     */
    public function getById(int $id, bool $check = false, int $userId = null, string $hash = null): Order
    {
        if (!$order = Order::load($id)) {
            throw new NotFoundException('Order not found');
        }

        if ($check) {
            if (!$hash && !$userId) {
                throw new NotFoundException('Order not found');
            }
            if ($hash && $order->getHash() != $hash) {
                throw new NotFoundException('Order not found');
            }
            if ($userId && $order->getUserId() != $userId) {
                throw new NotFoundException('Order not found');
            }
        }

        return $order;
    }

    /**
     * @param null $fuserId
     *
     * @return bool|OrderStorage
     */
    public function getStorage($fuserId = null)
    {
        if (!$fuserId) {
            $fuserId = Fuser::getId();
        }

        try {
            return $this->storageRepository->findByFuser($fuserId);
        } catch (NotFoundException $e) {
            return false;
        }
    }

    public function setStorageValuesFromRequest(OrderStorage $storage, Request $request): OrderStorage
    {
        // @todo set values from request
        return $storage;
    }

    /**
     * @param OrderStorage $storage
     *
     * @return bool
     */
    public function updateStorage(OrderStorage $storage, string $step = OrderService::AUTH_STEP): bool
    {
        try {
            return $this->storageRepository->save($storage, $step);
        } catch (NotFoundException $e) {
            return false;
        }
    }

    /**
     * @param $storage
     *
     * @return bool
     */
    public function clearStorage($storage): bool
    {
        try {
            return $this->storageRepository->clear($storage);
        } catch (NotFoundException $e) {
            return false;
        }
    }

    /**
     * @param OrderStorage $storage
     *
     * @return Order
     */
    public function createOrder(OrderStorage $storage): Order
    {
        // @todo create order
    }
}
