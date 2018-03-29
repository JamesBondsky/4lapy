<?php

namespace FourPaws\SaleBundle\Service;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderStorageSaveException;
use FourPaws\SaleBundle\Exception\OrderStorageValidationException;
use FourPaws\SaleBundle\Repository\OrderStorage\DatabaseStorageRepository;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class OrderStorageService
{
    const AUTH_STEP = 'auth';

    const DELIVERY_STEP = 'delivery';

    const PAYMENT_STEP = 'payment';

    const COMPLETE_STEP = 'complete';

    /**
     * Порядок оформления заказа
     * @var array
     */
    protected $stepOrder = [
        self::AUTH_STEP,
        self::DELIVERY_STEP,
        self::PAYMENT_STEP,
        self::COMPLETE_STEP,
    ];

    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    /**
     * @var PaymentCollection
     */
    protected $paymentCollection;

    /**
     * @var DatabaseStorageRepository
     */
    protected $storageRepository;

    /**
     * @var UserAccountService
     */
    protected $userAccountService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var CalculationResultInterface[]
     */
    protected $deliveries;

    /**
     * OrderStorageService constructor.
     *
     * @param BasketService $basketService
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param DatabaseStorageRepository $storageRepository
     * @param UserAccountService $userAccountService
     * @param DeliveryService $deliveryService
     */
    public function __construct(
        BasketService $basketService,
        CurrentUserProviderInterface $currentUserProvider,
        DatabaseStorageRepository $storageRepository,
        UserAccountService $userAccountService,
        DeliveryService $deliveryService
    ) {
        $this->basketService = $basketService;
        $this->currentUserProvider = $currentUserProvider;
        $this->storageRepository = $storageRepository;
        $this->userAccountService = $userAccountService;
        $this->deliveryService = $deliveryService;
    }

    /**
     * Вычисляет шаг оформления заказа в соответствии с состоянием хранилища
     *
     * @param OrderStorage $storage
     * @param string $startStep
     *
     * @return string
     */
    public function validateStorage(OrderStorage $storage, string $startStep = self::AUTH_STEP): string
    {
        $steps = array_reverse($this->stepOrder);
        $stepIndex = array_search($startStep, $steps, true);
        if ($stepIndex === false) {
            return $startStep;
        }

        $realStep = $startStep;
        $steps = \array_slice($steps, $stepIndex);
        foreach ($steps as $step) {
            if ($this->storageRepository->validate($storage, $step)->count()) {
                $realStep = $step;
            }
        }

        return $realStep;
    }

    /**
     * @param int|null $fuserId
     *
     * @throws OrderStorageSaveException
     * @return bool|OrderStorage
     */
    public function getStorage(int $fuserId = null)
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
                    'porch',
                    'floor',
                    'apartment',
                    'deliveryDate',
                    'deliveryInterval',
                    'deliveryPlaceCode',
                    'comment',
                    'partialGet',
                    'shopId',
                ];
                break;
            case self::PAYMENT_STEP:
                $availableValues = [
                    'paymentId',
                    'bonus',
                ];
        }

        $mapping = [
            'order-pick-time' => 'partialGet',
            'shopId'          => 'deliveryPlaceCode',
            'pay-type'        => 'paymentId',
        ];

        foreach ($request->request as $name => $value) {
            if (!\in_array($name, $availableValues, true) &&
                !\in_array($mapping[$name], $availableValues, true)
            ) {
                continue;
            }

            if (($name === 'bonus') && (!is_numeric($value))) {
                continue;
            }

            $setter = 'set' . ucfirst($name);
            if (method_exists($storage, $setter)) {
                $storage->$setter($value);
            } elseif (isset($mapping[$name])) {
                $setter = 'set' . ucfirst($mapping[$name]);
                if (method_exists($storage, $setter)) {
                    $storage->$setter($value);
                }
            }
        }

        return $storage;
    }

    /**
     * @param OrderStorage $storage
     * @param string $step
     *
     * @throws OrderStorageValidationException
     * @return bool
     */
    public function updateStorage(OrderStorage $storage, string $step = self::AUTH_STEP): bool
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
     * @throws \Exception
     * @throws NotFoundException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectNotFoundException
     * @return PaymentCollection
     */
    public function getPayments(OrderStorage $storage): PaymentCollection
    {
        if (!$deliveryId = $storage->getDeliveryId()) {
            throw new NotFoundException('No payments available');
        }

        if (!$this->paymentCollection) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $order = Order::create(
                SITE_ID,
                null,
                CurrencyManager::getBaseCurrency()
            );
            $this->paymentCollection = $order->getPaymentCollection();
            $sum = $this->basketService->getBasket()->getOrderableItems()->getPrice();

            if ($storage->getBonus()) {
                $innerPayment = $this->paymentCollection->getInnerPayment();
                $innerPayment->setField('SUM', $storage->getBonus());
                $sum -= $storage->getBonus();
            }

            $extPayment = $this->paymentCollection->createItem();
            $extPayment->setField('SUM', $sum);
        }

        return $this->paymentCollection;
    }

    /**
     * @param OrderStorage $storage
     * @param bool $withInner
     *
     * @throws \Exception
     * @throws NotFoundException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectNotFoundException
     * @return array
     */
    public function getAvailablePayments(OrderStorage $storage, $withInner = false): array
    {
        $paymentCollection = $this->getPayments($storage);

        $payments = [];
        /** @var Payment $payment */
        foreach ($paymentCollection as $payment) {
            if ($payment->isInner()) {
                continue;
            }

            $payments = PaySystemManager::getListWithRestrictions($payment);
        }

        if (!$withInner) {
            $innerPaySystemId = (int)PaySystemManager::getInnerPaySystemId();
            /** @var Payment $payment */
            foreach ($payments as $id => $payment) {
                if ($innerPaySystemId === $id) {
                    unset($payments[$id]);
                    break;
                }
            }
        }

        return $payments;
    }

    /**
     * @param OrderStorage $storage
     * @param bool $reload
     *
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @return CalculationResultInterface[]
     */


    /**
     * @param OrderStorage $storage
     * @param bool $reload
     *
     * @throws ArgumentException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws UserMessageException
     * @throws ApplicationCreateException
     * @throws DeliveryNotFoundException
     * @throws StoreNotFoundException
     * @return array
     */
    public function getDeliveries(OrderStorage $storage, $reload = false): array
    {
        if (null === $this->deliveries || $reload) {
            $this->deliveries = $this->deliveryService->getByBasket(
                $this->basketService->getBasket()->getOrderableItems(),
                '',
                [],
                $storage->getCurrentDate()
            );
        }

        return $this->deliveries;
    }

    /**
     * @param OrderStorage $storage
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws DeliveryNotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     * @throws UserMessageException
     * @return CalculationResultInterface
     */
    public function getSelectedDelivery(OrderStorage $storage): CalculationResultInterface
    {
        $deliveries = $this->getDeliveries($storage);
        $selectedDelivery = current($deliveries);
        if ($deliveryId = $storage->getDeliveryId()) {
            /** @var CalculationResultInterface $delivery */
            foreach ($deliveries as $delivery) {
                if ($storage->getDeliveryId() === $delivery->getDeliveryId()) {
                    $selectedDelivery = clone $delivery;
                    break;
                }
            }
        }

        if (!$selectedDelivery) {
            throw new NotFoundException('No deliveries available');
        }

        return $selectedDelivery;
    }

    /**
     * Получение максимального кол-ва бонусов, которыми можно оплатить заказ
     *
     * @param OrderStorage $storage
     *
     * @return float
     */
    public function getMaxBonusesForPayment(OrderStorage $storage): float
    {
        if (!$storage->getUserId()) {
            return 0;
        }

        $bonuses = 0;
        try {
            $this->userAccountService->refreshUserBalance();
            $bonuses = $this->userAccountService->findAccountByUser($this->currentUserProvider->getCurrentUser())
                                                ->getCurrentBudget();
        } catch (NotFoundException $e) {
        }

        $basket = $this->basketService->getBasket()->getOrderableItems();

        return floor(min($basket->getPrice() * OrderService::MAX_BONUS_PAYMENT, $bonuses));
    }

    /**
     * @param OrderStorage $storage
     *
     * @return array
     */
    public function storageToArray(OrderStorage $storage): array
    {
        return $this->storageRepository->toArray($storage);
    }
}
