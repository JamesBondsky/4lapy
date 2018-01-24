<?php

namespace FourPaws\SaleBundle\Service;

use Bitrix\Sale\Order;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Repository\OrderPropertyVariantRepository;
use FourPaws\SaleBundle\Repository\OrderStorage\StorageRepositoryInterface;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;

class OrderService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const AUTH_STEP = 'auth';

    const DELIVERY_STEP = 'delivery';

    const PAYMENT_STEP = 'payment';

    const COMPLETE_STEP = 'complete';

    const COMMUNICATION_SMS = '01';

    const COMMUNICATION_PHONE = '02';

    /**
     * @var StorageRepositoryInterface
     */
    protected $storageRepository;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    /**
     * @var OrderPropertyVariantRepository
     */
    protected $variantRepository;

    /**
     * OrderService constructor.
     *
     * @param StorageRepositoryInterface $orderStorageRepository
     * @param CurrentUserProviderInterface $currentUserProvider
     */
    public function __construct(
        StorageRepositoryInterface $orderStorageRepository,
        CurrentUserProviderInterface $currentUserProvider,
        OrderPropertyVariantRepository $variantRepository
    ) {
        $this->storageRepository = $orderStorageRepository;
        $this->currentUserProvider = $currentUserProvider;
        $this->variantRepository = $variantRepository;
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
            $fuserId = $this->currentUserProvider->getCurrentFUserId();
        }

        try {
            return $this->storageRepository->findByFuser($fuserId);
        } catch (NotFoundException $e) {
            return false;
        }
    }

    /**
     * @param OrderStorage $storage
     * @param Request $request
     * @param string $step
     *
     * @return OrderStorage
     */
    public function setStorageValuesFromRequest(OrderStorage $storage, Request $request, string $step): OrderStorage
    {
        /**
         * Чтобы нельзя было, например, обойти проверку капчи,
         * отправив в POST данные со всех форм разом
         */
        $availableValues = [];
        switch ($step) {
            case self::AUTH_STEP:
                $availableValues = [
                    'name',
                    'phone',
                    'email',
                    'altPhone',
                    'communicationWay',
                ];
                break;
        }

        foreach ($request->request as $name => $value) {
            if (!in_array($name, $availableValues)) {
                continue;
            }
            $setter = 'set' . ucfirst($name);
            if (method_exists($storage, $setter)) {
                $storage->$setter($value);
            }
        }

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

    /**
     * @param string $propertyCode
     * @param array $additionalFilter
     *
     * @return ArrayCollection
     * @throws InvalidArgumentException
     */
    public function getPropertyVariants(string $propertyCode, array $additionalFilter = []): ArrayCollection
    {
        return $this->variantRepository->getAvailableVariants($propertyCode, $additionalFilter);
    }
}
