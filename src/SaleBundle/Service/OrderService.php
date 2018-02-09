<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Service;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\ShipmentCollection;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\PersonalBundle\Exception\NotFoundException as AddressNotFoundException;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\SaleBundle\Collection\OrderPropertyCollection;
use FourPaws\SaleBundle\Collection\OrderPropertyVariantCollection;
use FourPaws\SaleBundle\Entity\OrderProperty;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Repository\OrderPropertyRepository;
use FourPaws\SaleBundle\Repository\OrderPropertyVariantRepository;
use FourPaws\SaleBundle\Repository\OrderStorage\StorageRepositoryInterface;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use FourPaws\UserBundle\Service\UserRegistrationProviderInterface;
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

    const PAYMENT_CASH = 'cash';

    const PAYMENT_CARD = 'card';

    const PAYMENT_ONLINE = 'card-online';

    const PAYMENT_INNER = 'inner';

    const PROPERTY_TYPE_ENUM = 'ENUM';

    /**
     * @var StorageRepositoryInterface
     */
    protected $storageRepository;

    /**
     * @var OrderPropertyVariantRepository
     */
    protected $variantRepository;

    /**
     * @var OrderPropertyRepository
     */
    protected $propertyRepository;

    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var CalculationResult[]
     */
    protected $deliveries;

    /**
     * @var PaymentCollection
     */
    protected $paymentCollection;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    /**
     * @var UserCitySelectInterface
     */
    protected $userCityProvider;

    /**
     * @var AddressService
     */
    protected $addressService;

    /**
     * @var UserRegistrationProviderInterface
     */
    protected $userRegistrationProvider;

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
     * @param StorageRepositoryInterface $storageRepository
     * @param OrderPropertyRepository $propertyRepository
     * @param OrderPropertyVariantRepository $variantRepository
     * @param BasketService $basketService
     * @param DeliveryService $deliveryService
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param UserCitySelectInterface $userCityProvider
     * @param AddressService $addressService
     * @param UserRegistrationProviderInterface $userRegistrationProvider
     */
    public function __construct(
        StorageRepositoryInterface $storageRepository,
        OrderPropertyRepository $propertyRepository,
        OrderPropertyVariantRepository $variantRepository,
        BasketService $basketService,
        DeliveryService $deliveryService,
        CurrentUserProviderInterface $currentUserProvider,
        UserCitySelectInterface $userCityProvider,
        AddressService $addressService,
        UserRegistrationProviderInterface $userRegistrationProvider
    ) {
        $this->basketService = $basketService;
        $this->deliveryService = $deliveryService;
        $this->variantRepository = $variantRepository;
        $this->storageRepository = $storageRepository;
        $this->currentUserProvider = $currentUserProvider;
        $this->userCityProvider = $userCityProvider;
        $this->propertyRepository = $propertyRepository;
        $this->addressService = $addressService;
        $this->userRegistrationProvider = $userRegistrationProvider;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Получение заказа по id
     *
     * @param int $id id заказа
     * @param bool $check выполнять ли проверки
     * @param int $userId id пользователя, к которому привязан заказ
     * @param string $hash хеш заказа (проверяется, если не передан userId)
     *
     * @throws NotFoundException
     * @return Order
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
            if ($hash && $order->getHash() !== $hash) {
                throw new NotFoundException('Order not found');
            }
            if ($userId && $order->getUserId() !== $userId) {
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
     * @throws OrderCreateException
     * @return Order
     */
    public function createOrder(OrderStorage $storage): Order
    {
        $order = Order::create(SITE_ID);
        $selectedCity = $this->userCityProvider->getSelectedCity();

        /**
         * Привязываем корзину
         */
        /** @noinspection PhpParamsInspection */
        $order->setBasket($this->basketService->getBasket()->getOrderableItems());

        /**
         * Задание способов оплаты
         */
        if ($storage->getPaymentId()) {
            $this->getPayments($storage, $order);
            /** @var Payment $payment */
            foreach ($this->getPayments($storage, $order) as $payment) {
                if (!$payment->isInner()) {
                    $payment->setField('PAY_SYSTEM_ID', $storage->getPaymentId());
                }
            }
        } else {
            throw new OrderCreateException('Не выбран способ оплаты');
        }

        /**
         * Задание способов доставки
         */
        if ($storage->getDeliveryId()) {
            $locationProp = $order->getPropertyCollection()->getDeliveryLocation();
            if (!$locationProp) {
                throw new OrderCreateException('Отсутствует свойство привязки к местоположению');
            }
            $locationProp->setValue($selectedCity['CODE']);

            $shipmentCollection = $order->getShipmentCollection();
            $shipment = $shipmentCollection->createItem();
            $shipmentItemCollection = $shipment->getShipmentItemCollection();
            $shipment->setField('CURRENCY', $order->getCurrency());
            /** @var BasketItem $item */
            foreach ($order->getBasket() as $item) {
                $shipmentItem = $shipmentItemCollection->createItem($item);
                $shipmentItem->setQuantity($item->getQuantity());
            }
            /** @var ShipmentCollection $shipmentCollection */
            $shipmentCollection = $shipment->getCollection();
            $deliveries = $this->getDeliveries();
            $selectedDelivery = null;
            /** @var CalculationResult $delivery */
            foreach ($deliveries as $delivery) {
                if ($storage->getDeliveryId() === (int)$delivery->getData()['DELIVERY_ID']) {
                    $selectedDelivery = $delivery;
                }
            }

            if (null === $selectedDelivery) {
                throw new OrderCreateException('Не выбрана доставка');
            }

            $shipment->setFields(
                [
                    'DELIVERY_ID'   => $selectedDelivery->getData()['DELIVERY_ID'],
                    'DELIVERY_NAME' => $selectedDelivery->getData()['DELIVERY_NAME'],
                    'CURRENCY'      => $order->getCurrency(),
                ]
            );

            $shipmentCollection->calculateDelivery();
        } else {
            throw new OrderCreateException('Не выбрана доставка');
        }

        /**
         * Обработка полей заказа
         */
        if ($storage->getComment()) {
            $order->setField('USER_DESCRIPTION', $storage->getComment());
        }

        $address = null;
        if ($storage->getAddressId()) {
            try {
                $address = $this->addressService->getById($storage->getAddressId());
                $storage->setStreet($address->getStreet())
                        ->setHouse($address->getHouse())
                        ->setBuilding($address->getHousing())
                        ->setFloor($address->getFloor())
                        ->setApartment($address->getFlat())
                        ->setPorch($address->getEntrance());
            } catch (AddressNotFoundException $e) {
            }
        }

        /**
         * Обработка свойств заказа
         */
        $propertyValueCollection = $order->getPropertyCollection();
        $arrayStorage = $this->storageRepository->toArray($storage);
        /** @var OrderProperty $orderProperty */

        $deliveryDate = $this->deliveryService->getStockResultByDelivery($selectedDelivery)
                                              ->getDeliveryDate();

        /** @var PropertyValue $propertyValue */
        foreach ($propertyValueCollection as $propertyValue) {
            $code = $propertyValue->getProperty()['CODE'];
            $key = 'PROPERTY_' . $code;

            if (!empty($arrayStorage[$key])) {
                $value = $arrayStorage[$key];
            } else {
                switch ($code) {
                    case 'DELIVERY_PLACE_CODE':
                        if (!$this->deliveryService->isInnerPickup($selectedDelivery)) {
                            continue 2;
                        }
                        $value = $storage->getDeliveryPlaceCode();
                        break;
                    case 'DPD_TERMINAL_CODE':
                        if (!$this->deliveryService->isDpdPickup($selectedDelivery)) {
                            continue 2;
                        }
                        $value = $storage->getDeliveryPlaceCode();
                        break;
                    case 'DELIVERY_DATE':
                        /**
                         * У доставок есть выбор даты доставки
                         */
                        $date = clone $deliveryDate;
                        if ($this->deliveryService->isDelivery($selectedDelivery)) {
                            if (($days = $storage->getDeliveryDate() - 1) >= 0) {
                                $date->modify('+' . $days . ' days');
                            }
                        }
                        $value = $date->format('d.m.Y');
                        break;
                    case 'DELIVERY_INTERVAL':
                        /**
                         * У доставок есть выбор интервала доставки
                         */
                        if ($this->deliveryService->isDelivery($selectedDelivery)) {
                            if (($index = $storage->getDeliveryInterval() - 1) < 0) {
                                continue 2;
                            }
                            if (!$interval = $selectedDelivery->getData()['INTERVALS'][$index]) {
                                continue 2;
                            }

                            $value = sprintf(
                                '%s:00-%s:00',
                                str_pad($interval['FROM'], 2, '0', STR_PAD_LEFT),
                                str_pad($interval['TO'], 2, '0', STR_PAD_LEFT)
                            );
                        } else {
                            $value = sprintf(
                                '%s:00-23:59',
                                $deliveryDate->format('H')
                            );
                        }

                        break;
                    default:
                        continue 2;
                }
            }

            $propertyValue->setValue($value);
        }

        /**
         * Три ситуации:
         * 1) Если юзер авторизован, то привязываем заказ к нему
         * 2) Если не авторизован, но телефон совпадает с телефоном существующего пользователя,
         *    то заказ привязывается к этому пользователю. Авторизации не происходит
         * 3) Если не авторизован, а номер телефона отсутствует в базе,
         *    то происходит регистрация. Авторизации не происходит
         */
        $needCreateAddress = false;
        $addressUserId = null;
        if ($storage->getUserId()) {
            $order->setFieldNoDemand('USER_ID', $storage->getUserId());
            $user = $this->currentUserProvider->getCurrentUser();
            if (!$user->getEmail() && $storage->getEmail()) {
                $user->setEmail($storage->getEmail());
                $this->currentUserProvider->getUserRepository()->updateEmail(
                    $user->getId(),
                    $storage->getEmail()
                );
            }
            if (!$address) {
                $needCreateAddress = true;
                $addressUserId = $storage->getUserId();
            }
        } else {
            $users = $this->currentUserProvider->getUserRepository()->findBy(
                ['PERSONAL_PHONE' => $storage->getPhone()]
            );
            if ($user = reset($users)) {
                $order->setFieldNoDemand('USER_ID', $user->getId());
            } else {
                $user = (new User())->setName($storage->getName())
                                    ->setEmail($storage->getEmail())
                                    ->setLogin($storage->getPhone())
                                    ->setPassword(randString(6))
                                    ->setPersonalPhone($storage->getPhone());
                $this->userRegistrationProvider->register($user, true);
                $order->setFieldNoDemand('USER_ID', $user->getId());
                $addressUserId = $user->getId();
                $needCreateAddress = true;
            }
        }

        /**
         * Сохраняем адрес, если:
         * 1) пользователь только что зарегистрирован
         * 2) авторизованный пользователь задал новый адрес
         */
        if ($needCreateAddress) {
            $address = (new Address())->setCity($storage->getCity())
                                      ->setCityLocation($storage->getCityCode())
                                      ->setUserId($addressUserId)
                                      ->setStreet($storage->getStreet())
                                      ->setHouse($storage->getHouse())
                                      ->setHousing($storage->getBuilding())
                                      ->setEntrance($storage->getPorch())
                                      ->setFloor($storage->getFloor())
                                      ->setFlat($storage->getApartment());

            $this->addressService->add($address);
        }

        $result = $order->save();
        if (!$result->isSuccess()) {
            throw new OrderCreateException(implode(', ', $result->getErrorMessages()));
        }

        $this->storageRepository->clear($storage);

        return $order;
    }

    /**
     * @return OrderPropertyCollection
     */
    public function getProperties(): OrderPropertyCollection
    {
        return $this->propertyRepository->findBy();
    }

    /**
     * @param int $id
     *
     * @return OrderProperty
     */
    public function getPropertyById(int $id): OrderProperty
    {
        return $this->propertyRepository->findById($id);
    }

    /**
     * @param string $code
     *
     * @return OrderProperty
     */
    public function getPropertyByCode(string $code): OrderProperty
    {
        return $this->propertyRepository->findByCode($code);
    }

    /**
     * @param OrderProperty $property
     *
     * @return OrderPropertyVariantCollection
     */
    public function getPropertyVariants(OrderProperty $property): OrderPropertyVariantCollection
    {
        return $this->variantRepository->findByProperty($property);
    }

    /**
     * @param bool $reload
     *
     * @return CalculationResult[]
     */
    public function getDeliveries($reload = false): array
    {
        if (null === $this->deliveries || $reload) {
            $this->deliveries = $this->deliveryService->getByBasket(
                $this->basketService->getBasket()->getOrderableItems()
            );
        }

        return $this->deliveries;
    }

    /**
     * Вычисляет шаг оформления заказа в соответствии с состоянием хранилища
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

    /**
     * @param string $code
     *
     * @throws NotFoundException
     * @return int
     */
    public function getPaymentIdByCode(string $code): int
    {
        $payment = PaySystemActionTable::getList(['filter' => ['CODE' => $code]])->fetch();
        if (!$payment) {
            throw new NotFoundException('Payment system not found');
        }

        return (int)$payment['ID'];
    }

    /**
     * @param OrderStorage $storage
     * @param null|Order $order
     *
     * @throws NotFoundException
     * @return PaymentCollection
     */
    public function getPayments(OrderStorage $storage, Order $order = null): PaymentCollection
    {
        if (!$deliveryId = $storage->getDeliveryId()) {
            throw new NotFoundException('No payments available');
        }

        if (!$this->paymentCollection) {
            if (!$order instanceof Order) {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $order = Order::create(
                    SITE_ID,
                    null,
                    CurrencyManager::getBaseCurrency()
                );
            }
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
}
