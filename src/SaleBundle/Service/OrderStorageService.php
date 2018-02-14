<?php

namespace FourPaws\SaleBundle\Service;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderStorageValidationException;
use FourPaws\SaleBundle\Repository\OrderStorage\DatabaseStorageRepository;
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
     * OrderStorageService constructor.
     *
     * @param BasketService $basketService
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param DatabaseStorageRepository $storageRepository
     */
    public function __construct(
        BasketService $basketService,
        CurrentUserProviderInterface $currentUserProvider,
        DatabaseStorageRepository $storageRepository
    ) {
        $this->basketService = $basketService;
        $this->currentUserProvider = $currentUserProvider;
        $this->storageRepository = $storageRepository;
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
     * @param int $fuserId
     *
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
                    'dpdTerminalCode',
                    'comment',
                    'partialGet',
                ];
                break;
            case self::PAYMENT_STEP:
                $availableValues = [
                    'paymentId',
                    'bonusSum',
                ];
        }

        foreach ($request->request as $name => $value) {
            if (!\in_array($name, $availableValues, true)) {
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

            if ($storage->hasBonusPayment() && $storage->getBonusSum()) {
                $innerPayment = $this->paymentCollection->getInnerPayment();
                $innerPayment->setField('SUM', $storage->getBonusSum());
                $sum -= $storage->getBonusSum();
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
     *
     * @return array
     */
    public function storageToArray(OrderStorage $storage): array
    {
        return $this->storageRepository->toArray($storage);
    }
}
