<?php

namespace FourPaws\SaleBundle\Service;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DpdPickupResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderStorageSaveException;
use FourPaws\SaleBundle\Exception\OrderStorageValidationException;
use FourPaws\SaleBundle\Repository\OrderStorage\DatabaseStorageRepository;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class OrderStorageService
{
    public const NOVALIDATE_STEP = 'novalidate';

    public const AUTH_STEP = 'auth';

    public const DELIVERY_STEP = 'delivery';

    public const PAYMENT_STEP = 'payment';

    public const PAYMENT_STEP_CARD = 'payment-card';

    public const COMPLETE_STEP = 'complete';

    public const SESSION_EXPIRED_VIOLATION = 'session-expired';

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
     * @param DeliveryService $deliveryService
     */
    public function __construct(
        BasketService $basketService,
        CurrentUserProviderInterface $currentUserProvider,
        DatabaseStorageRepository $storageRepository,
        DeliveryService $deliveryService
    ) {
        $this->basketService = $basketService;
        $this->currentUserProvider = $currentUserProvider;
        $this->storageRepository = $storageRepository;
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

        $realStep = $startStep;
        if ($stepIndex !== false) {
            $steps = \array_slice($steps, $stepIndex);
            foreach ($steps as $step) {
                if ($this->storageRepository->validate($storage, $step)->count()) {
                    $realStep = $step;
                }
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
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @return OrderStorage
     */
    public function setStorageValuesFromRequest(OrderStorage $storage, Request $request, string $step): OrderStorage
    {
        $data = $request->request->all();

        $mapping = [
            'order-pick-time' => 'split',
            'shopId' => 'deliveryPlaceCode',
            'pay-type' => 'paymentId',
            'cardNumber' => 'discountCardNumber'
        ];

        foreach ($data as $name => $value) {
            if (isset($mapping[$name])) {
                $data[$mapping[$name]] = $value;
                unset($data[$name]);
            }
        }

        /**
         * Чтобы нельзя было, например, обойти проверку капчи,
         * отправив в POST данные со всех форм разом
         */
        $availableValues = [];
        switch ($step) {
            case self::AUTH_STEP:
                $availableValues = [
                    'altPhone',
                    'communicationWay',
                    'captchaFilled',
                ];

                if (!$storage->getUserId()) {
                    $availableValues[] = 'name';
                    $availableValues[] = 'phone';
                    $availableValues[] = 'email';
                } else {
                    try {
                        $user = $this->currentUserProvider->getCurrentUser();
                        if ($user &&
                            !$user->getEmail() &&
                            $storage->getUserId() === $user->getId()
                        ) {
                            $availableValues[] = 'email';
                        }
                    } catch (NotAuthorizedException $e) {
                    }
                }

                break;
            case self::DELIVERY_STEP:
                try {
                    $deliveryCode = $this->deliveryService->getDeliveryCodeById(
                        (int)$data['deliveryId']
                    );
                    if (\in_array($deliveryCode, DeliveryService::DELIVERY_CODES, true)) {
                        switch ($data['delyveryType']) {
                            case 'twoDeliveries':
                                $data['deliveryInterval'] = $data['deliveryInterval1'];
                                $data['secondDeliveryInterval'] = $data['deliveryInterval2'];
                                $data['deliveryDate'] = $data['deliveryDate1'];
                                $data['secondDeliveryDate'] = $data['deliveryDate2'];
                                $data['comment'] = $data['comment1'];
                                $data['secondComment'] = $data['comment2'];
                                $data['split'] = 1;
                                break;
                            default:
                                $data['split'] = 0;
                        }
                    } elseif ((int)$data['split'] === 1) {
                        $tmpStorage = clone $storage;
                        $tmpStorage->setDeliveryId($data['deliveryId']);
                        $pickup = $this->getSelectedDelivery($tmpStorage);
                        if (!$this->canSplitOrder($pickup) && !$this->canGetPartial($pickup)) {
                            $data['split'] = 0;
                        }
                    }
                } catch (DeliveryNotFoundException $e) {
                }

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
                    'split',
                    'secondDeliveryDate',
                    'secondDeliveryInterval',
                    'secondComment'
                ];
                break;
            case self::PAYMENT_STEP:
                $availableValues = [
                    'paymentId',
                    'bonus',
                ];
                break;
            case self::PAYMENT_STEP_CARD:
                $availableValues = [
                    'discountCardNumber'
                ];
                break;
        }

        foreach ($data as $name => $value) {
            if (null === $value) {
                continue;
            }

            if (!\in_array($name, $availableValues, true)) {
                continue;
            }

            if (($name === 'bonus') && (!is_numeric($value))) {
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
     * @param bool $filter
     *
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws DeliveryNotFoundException
     * @throws NotImplementedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @return array
     */
    public function getAvailablePayments(OrderStorage $storage, $withInner = false, $filter = true): array
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

        /**
         * Если выбран самовывоз DPD и терминал, то оставляем только доступные в этом терминале способы оплаты
         */
        if ($storage->getDeliveryPlaceCode() && $storage->getDeliveryId()) {
            $deliveryCode = $this->deliveryService->getDeliveryCodeById($storage->getDeliveryId());
            if ($this->deliveryService->isDpdPickupCode($deliveryCode) &&
                $terminal = $this->deliveryService->getDpdTerminalByCode($storage->getDeliveryPlaceCode())
            ) {
                foreach ($payments as $id => $payment) {
                    $delete = false;
                    switch ($payment['CODE']) {
                        case OrderService::PAYMENT_CASH_OR_CARD:
                            $delete = !$terminal->hasCardPayment();
                            break;
                        case OrderService::PAYMENT_CASH:
                            $delete = !$terminal->hasCashPayment();
                            break;
                    }

                    if ($delete) {
                        unset($payments[$id]);
                    }
                }
            }
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

        /**
         * Если есть оплата "наличными или картой", удаляем оплату "наличными"
         */
        if ($filter && !empty(\array_filter($payments, function ($item) {
            return $item['CODE'] === OrderService::PAYMENT_CASH_OR_CARD;
        }))) {
            foreach ($payments as $id => $payment) {
                if ($payment['CODE'] === OrderService::PAYMENT_CASH) {
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
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws UserMessageException
     * @throws ApplicationCreateException
     * @throws DeliveryNotFoundException
     * @throws StoreNotFoundException
     * @return CalculationResultInterface[]
     */
    public function getDeliveries(OrderStorage $storage, $reload = false): array
    {
        if (null === $this->deliveries || $reload) {
            $basket = $this->basketService->getBasket();
            if (null === $basket->getOrder()) {
                $order = Order::create(SITE_ID);
                $order->setBasket($basket);
            }

            $this->deliveries = $this->deliveryService->getByBasket(
                $basket,
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
     * @throws NotFoundException
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
                    $selectedDelivery = $delivery;
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
     * @param OrderStorage $storage
     *
     * @return array
     */
    public function storageToArray(OrderStorage $storage): array
    {
        return $this->storageRepository->toArray($storage);
    }

    /**
     * Можно ли разделить заказ
     *
     * @param CalculationResultInterface $delivery
     *
     * @return bool
     */
    public function canSplitOrder(CalculationResultInterface $delivery): bool
    {
        $result = false;
        /**
         * Для самовывоза DPD разделения заказов нет
         */
        if (!$delivery instanceof DpdPickupResult) {
            if (!($this->deliveryService->isDelivery($delivery) &&
                $delivery->getStockResult()->getOrderable()->getByRequest()->isEmpty())
            ) {
                [$available, $delayed] = $this->splitStockResult($delivery);

                $result = !$available->isEmpty() && !$delayed->isEmpty();
            }
        }
        return $result;
    }

    /**
     * Возможно ли частичное получение заказа
     *
     * @param CalculationResultInterface $delivery
     *
     * @return bool
     */
    public function canGetPartial(CalculationResultInterface $delivery): bool
    {
        $result = false;
        if ($delivery->getDeliveryCode() === DeliveryService::INNER_PICKUP_CODE &&
            $delivery->getStockResult()->getByRequest()->isEmpty() &&
            !$delivery->getStockResult()->getAvailable()->isEmpty() &&
            !$delivery->getStockResult()->getDelayed()->isEmpty()
        ) {
            $result = true;
        }
        return $result;
    }

    /**
     * @param CalculationResultInterface $delivery
     *
     * @return StockResultCollection[]
     */
    public function splitStockResult(CalculationResultInterface $delivery): array
    {
        $stockResultCollection = $delivery->getStockResult();
        if ($delivery->getStockResult()->getByRequest()->isEmpty()) {
            $available = $stockResultCollection->getAvailable();
            $delayed = $stockResultCollection->getDelayed();
        } else {
            $available = $stockResultCollection->getRegular();
            $delayed = $stockResultCollection->getByRequest();
        }

        return [$available, $delayed];
    }
}
