<?php

namespace FourPaws\SaleBundle\Service;

use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Order;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\ReCaptcha\ReCaptchaService;
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
     * @var OrderPropertyVariantRepository
     */
    protected $variantRepository;

    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var CalculationResult[]
     */
    protected $deliveries;

    /**
     * Порядок оформления заказа
     * @var array
     */
    protected $stepOrder = [
        OrderService::AUTH_STEP,
        OrderService::DELIVERY_STEP,
        OrderService::PAYMENT_STEP,
        OrderService::COMPLETE_STEP,
    ];

    /**
     * OrderService constructor.
     *
     * @param StorageRepositoryInterface $orderStorageRepository
     * @param OrderPropertyVariantRepository $variantRepository
     */
    public function __construct(
        StorageRepositoryInterface $storageRepository,
        OrderPropertyVariantRepository $variantRepository
    ) {
        $this->variantRepository = $variantRepository;
        $this->storageRepository = $storageRepository;
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
            $fuserId = $this->container->get(CurrentUserProviderInterface::class)->getCurrentFUserId();
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
                    'captchaFilled',
                ];
                break;
            case self::DELIVERY_STEP:
                $availableValues = [
                    'deliveryId',
                    'addressId',
                    'street',
                    'house',
                    'building',
                    'floor',
                    'apartment',
                    'deliveryDate',
                    'deliveryInterval',
                    'deliveryPlaceCode',
                    'dpdTerminalCode',
                    'comment',
                ];
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

    /**
     * @param bool $reload
     *
     * @return CalculationResult[]
     */
    public function getDeliveries($reload = false): array
    {
        if (null === $this->deliveries || $reload) {
            /** @var DeliveryService $deliveryService */
            $deliveryService = $this->container->get('delivery.service');
            /** @var BasketService $basketService */
            $basketService = $this->container->get(BasketService::class);
            $basket = $basketService->getBasket()->getOrderableItems();
            $this->deliveries = $deliveryService->getByBasket($basket);
        }

        return $this->deliveries;
    }

    /**
     * Вычисляет шаг оформления заказа в соотвествии с состоянием хранилища
     *
     * @param OrderStorage $storage
     * @param string $startStep
     */
    public function validateStorage(OrderStorage $storage, string $startStep = self::AUTH_STEP): string
    {
        $steps = array_reverse($this->stepOrder);
        $stepIndex = array_search($startStep, $steps);
        if ($stepIndex === false) {
            return $startStep;
        }

        $realStep = $startStep;
        $steps = array_slice($steps, $stepIndex);
        foreach ($steps as $step) {
            if ($this->storageRepository->validate($storage, $step)->count()) {
                $realStep = $step;
            }
        }

        return $realStep;
    }
}
