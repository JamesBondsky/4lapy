<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\NotFoundException as AddressNotFoundException;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\SaleBundle\Entity\OrderSplitResult;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Exception\OrderSplitException;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\DeliveryScheduleResult;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use FourPaws\UserBundle\Service\UserRegistrationProviderInterface;
use Psr\Log\LoggerAwareInterface;

class OrderService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const PAYMENT_CASH = 'cash';

    public const PAYMENT_CARD = 'card';

    public const PAYMENT_ONLINE = 'card-online';

    public const PAYMENT_INNER = 'inner';

    public const PROPERTY_TYPE_ENUM = 'ENUM';

    /**
     * Дефолтный статус заказа при курьерской доставке
     */
    public const STATUS_NEW_COURIER = 'Q';

    /**
     * Дефолтный статус заказа при самовывозе
     */
    public const STATUS_NEW_PICKUP = 'N';

    /**
     * Заказ доставляется ("Исполнен" для курьерской доставки)
     */
    public const STATUS_DELIVERING = 'Y';

    /**
     * Заказ в пункте выдачи
     */
    public const STATUS_ISSUING_POINT = 'F';

    /**
     * Заказ доставлен
     */
    public const STATUS_DELIVERED = 'J';

    /**
     * 90% заказа можно оплатить бонусами
     */
    public const MAX_BONUS_PAYMENT = 0.9;

    /**
     * @var AddressService
     */
    protected $addressService;

    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var OrderStorageService
     */
    protected $orderStorageService;

    /**
     * @var UserCitySelectInterface
     */
    protected $userCityProvider;

    /**
     * @var UserRegistrationProviderInterface
     */
    protected $userRegistrationProvider;

    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * OrderService constructor.
     *
     * @param AddressService $addressService
     * @param BasketService $basketService
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param DeliveryService $deliveryService
     * @param StoreService $storeService
     * @param OrderStorageService $orderStorageService
     * @param UserCitySelectInterface $userCityProvider
     * @param UserRegistrationProviderInterface $userRegistrationProvider
     */
    public function __construct(
        AddressService $addressService,
        BasketService $basketService,
        CurrentUserProviderInterface $currentUserProvider,
        DeliveryService $deliveryService,
        StoreService $storeService,
        OrderStorageService $orderStorageService,
        UserCitySelectInterface $userCityProvider,
        UserRegistrationProviderInterface $userRegistrationProvider
    ) {
        $this->addressService = $addressService;
        $this->basketService = $basketService;
        $this->currentUserProvider = $currentUserProvider;
        $this->deliveryService = $deliveryService;
        $this->storeService = $storeService;
        $this->orderStorageService = $orderStorageService;
        $this->userCityProvider = $userCityProvider;
        $this->userRegistrationProvider = $userRegistrationProvider;
        $this->withLogName('OrderService');
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
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @return Order
     */
    public function getOrderById(int $id, bool $check = false, int $userId = null, string $hash = null): Order
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

            if ($userId && (int)$order->getUserId() !== $userId) {
                throw new NotFoundException('Order not found');
            }
        }

        return $order;
    }

    /**
     * @param OrderStorage $storage
     * @param Basket|null $basket
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws DeliveryNotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderCreateException
     * @throws StoreNotFoundException
     * @throws UserMessageException
     * @return Order
     */
    public function initOrder(OrderStorage $storage, ?Basket $basket = null): Order
    {
        $order = Order::create(SITE_ID);
        $selectedCity = $this->userCityProvider->getSelectedCity();

        /**
         * Привязываем корзину
         */
        /** @noinspection PhpParamsInspection */
        $basket = $basket ?? $this->basketService->getBasket();
        $order->setBasket($basket);
        if ($order->getBasket()->getOrderableItems()->isEmpty()) {
            throw new OrderCreateException('Корзина пуста');
        }

        try {
            $selectedDelivery = $this->orderStorageService->getSelectedDelivery($storage);
        } catch (NotFoundException $e) {
            throw new OrderCreateException('Нет доступных доставок');
        }
        $selectedDelivery = clone $selectedDelivery;

        /**
         * Задание способов доставки
         */
        $propertyValueCollection = $order->getPropertyCollection();
        if ($storage->getDeliveryId()) {
            $locationProp = $order->getPropertyCollection()->getDeliveryLocation();
            if (!$locationProp) {
                throw new OrderCreateException('Отсутствует свойство привязки к местоположению');
            }
            $locationProp->setValue($selectedCity['CODE']);

            $selectedDelivery->setDateOffset($storage->getDeliveryDate());
            if (($intervalIndex = $storage->getDeliveryInterval() - 1) >= 0) {
                /** @var Interval $interval */
                if ($interval = $selectedDelivery->getAvailableIntervals()[$intervalIndex]) {
                    $selectedDelivery->setSelectedInterval($interval);
                }
            }

            if ($this->deliveryService->isDelivery($selectedDelivery)) {
                /** @noinspection PhpInternalEntityUsedInspection */
                $order->setFieldNoDemand('STATUS_ID', static::STATUS_NEW_COURIER);
            }

            $shipmentCollection = $order->getShipmentCollection();
            $shipment = $shipmentCollection->createItem();
            $shipmentItemCollection = $shipment->getShipmentItemCollection();
            try {
                /** @var BasketItem $item */
                foreach ($order->getBasket() as $item) {
                    $shipmentItem = $shipmentItemCollection->createItem($item);
                    $shipmentItem->setQuantity($item->getQuantity());
                }

                $shipment->setFields(
                    [
                        'CURRENCY',
                        $order->getCurrency(),
                        'DELIVERY_ID' => $selectedDelivery->getDeliveryId(),
                        'DELIVERY_NAME' => $selectedDelivery->getDeliveryName(),
                        'CURRENCY' => $order->getCurrency(),
                        'PRICE_DELIVERY' => $selectedDelivery->getPrice(),
                        'CUSTOM_PRICE_DELIVERY' => 'Y',
                    ]
                );
            } catch (\Exception $e) {
                $this->log()->error('failed to set shipment fields', [
                    'deliveryId' => $selectedDelivery->getDeliveryId(),
                ]);
                throw new OrderCreateException('О май гад, мы все умрем');
            }

            $shipmentCollection->calculateDelivery();

            $deliveryDate = $selectedDelivery->getDeliveryDate();

            /**
             * Задание свойств заказа, связанных с доставкой
             */
            /** @var PropertyValue $propertyValue */
            foreach ($propertyValueCollection as $propertyValue) {
                $code = $propertyValue->getProperty()['CODE'];
                switch ($code) {
                    case 'SHIPMENT_PLACE_CODE':
                        $shipmentResult = $selectedDelivery->getShipmentResult();
                        if ($shipmentResult instanceof DeliveryScheduleResult) {
                            $value = $shipmentResult->getSchedule()->getSenderCode();
                        } else {
                            continue 2;
                        }
                        break;
                    case 'DELIVERY_PLACE_CODE':
                        if ($this->deliveryService->isInnerPickup($selectedDelivery)) {
                            $value = $storage->getDeliveryPlaceCode();
                        } else {
                            $value = $selectedDelivery->getSelectedStore()->getXmlId();
                        }
                        break;
                    case 'DPD_TERMINAL_CODE':
                        if (!$this->deliveryService->isDpdPickup($selectedDelivery)) {
                            continue 2;
                        }
                        $value = $storage->getDeliveryPlaceCode();
                        break;
                    case 'DELIVERY_DATE':
                        $value = $selectedDelivery->getDeliveryDate()->format('d.m.Y');
                        break;
                    case 'DELIVERY_INTERVAL':
                        /**
                         * У доставок есть выбор интервала доставки
                         */
                        if ($this->deliveryService->isDelivery($selectedDelivery)) {
                            if ($interval = $selectedDelivery->getSelectedInterval()) {
                                $value = sprintf(
                                    '%s:00-%s:00',
                                    str_pad($interval->getFrom(), 2, '0', STR_PAD_LEFT),
                                    str_pad($interval->getTo(), 2, '0', STR_PAD_LEFT)
                                );
                            } else {
                                continue 2;
                            }
                        } else {
                            $value = sprintf(
                                '%s:00-23:59',
                                $deliveryDate->format('H')
                            );
                        }

                        break;
                    case 'REGION_COURIER_FROM_DC':
                        $value = $selectedDelivery->getStockResult()->getDelayed()->isEmpty()
                            ? BitrixUtils::BX_BOOL_FALSE
                            : BitrixUtils::BX_BOOL_TRUE;
                        break;
                    default:
                        continue 2;
                }

                $propertyValue->setValue($value);
            }
        }

        /**
         * Задание способов оплаты
         */
        if ($storage->getPaymentId()) {
            $paymentCollection = $order->getPaymentCollection();
            $sum = $order->getBasket()->getOrderableItems()->getPrice();
            $sum += $order->getDeliveryPrice();

            /**
             * Нужно для оплаты бонусами
             */
            if ($storage->getUserId()) {
                /** @noinspection PhpInternalEntityUsedInspection */
                $order->setFieldNoDemand('USER_ID', $storage->getUserId());
            }

            try {
                if ($storage->getBonus()) {
                    $innerPayment = $paymentCollection->getInnerPayment();
                    $innerPayment->setField('SUM', $storage->getBonus());
                    $innerPayment->setPaid('Y');
                    $sum -= $storage->getBonus();
                }
            } catch (\Exception $e) {

            }

            try {
                $extPayment = $paymentCollection->createItem();
                $extPayment->setField('SUM', $sum);
                $extPayment->setField('PAY_SYSTEM_ID', $storage->getPaymentId());
                /** @var \Bitrix\Sale\PaySystem\Service $paySystem */
                $paySystem = $extPayment->getPaySystem();
                $extPayment->setField('PAY_SYSTEM_NAME', $paySystem->getField('NAME'));
            } catch (\Exception $e) {

            }
        }

        /**
         * Обработка полей заказа
         */
        if ($storage->getComment()) {
            $order->setField('USER_DESCRIPTION', $storage->getComment());
        } else {
            $order->setField('USER_DESCRIPTION', '');
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
                $storage->setAddressId(0);
            }
        }

        /**
         * Обработка свойств заказа
         */
        $arrayStorage = $this->orderStorageService->storageToArray($storage);

        $addressProperties = [
            'STREET',
            'HOUSE',
            'BUILDING',
            'APARTMENT',
            'PORCH',
            'FLOOR'
        ];
        $skipAddressProperties = !$this->deliveryService->isDelivery($selectedDelivery);

        /** @var PropertyValue $propertyValue */
        foreach ($propertyValueCollection as $propertyValue) {
            $code = $propertyValue->getProperty()['CODE'];
            if ($skipAddressProperties && \in_array($code, $addressProperties, true)) {
                continue;
            }

            $key = 'PROPERTY_' . $code;

            $value = $arrayStorage[$key] ?? null;

            /**
             * Если у заказа самовывоз из магазина или курьерская доставка из зоны 2,
             * и в наличии более 90% от суммы заказа, при этом в случае курьерской доставки имеются отложенные товары,
             * то способ коммуникации изменяется на "Телефонный звонок (анализ)"
             */
            if ($selectedDelivery &&
                $code === 'COM_WAY' &&
                ($this->deliveryService->isInnerPickup($selectedDelivery) || $this->deliveryService->isInnerDelivery($selectedDelivery))
            ) {
                $changeCommunicationWay = false;
                $stockResult = $selectedDelivery->getStockResult();
                if ($this->deliveryService->isInnerPickup($selectedDelivery)) {
                    $changeCommunicationWay = true;
                } elseif ($this->deliveryService->isInnerDelivery($selectedDelivery)) {
                    if (($selectedDelivery->getDeliveryZone() === DeliveryService::ZONE_2) &&
                        !$stockResult->getDelayed()->isEmpty()
                    ) {
                        $changeCommunicationWay = true;
                    }
                }
                if ($changeCommunicationWay) {
                    $totalPrice = $order->getBasket()->getOrderableItems()->getPrice();
                    $availablePrice = $stockResult->getAvailable()->getPrice();
                    if ($availablePrice > $totalPrice * 0.9) {
                        $value = OrderPropertyService::COMMUNICATION_PHONE_ANALYSIS;
                    }
                }
            }
            if (null !== $value) {
                $propertyValue->setValue($value);
            }
        }

        return $order;
    }

    /**
     * @param Order $order
     * @param OrderStorage $storage
     * @param bool $fastOrder
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws DeliveryNotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws OrderCreateException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws UserMessageException
     */
    public function saveOrder(Order $order, OrderStorage $storage, bool $fastOrder = false): void
    {
        try {
            $selectedDelivery = $this->orderStorageService->getSelectedDelivery($storage);
        } catch (NotFoundException $e) {
            throw new OrderCreateException('No deliveries available');
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
        $newUser = false;
        if ($storage->getUserId()) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $order->setFieldNoDemand('USER_ID', $storage->getUserId());
            $user = $this->currentUserProvider->getCurrentUser();
            if (!$user->getDiscountCardNumber() && $storage->getDiscountCardNumber()) {
                $this->currentUserProvider->getUserRepository()->updateDiscountCard(
                    $user->getId(),
                    $storage->getDiscountCardNumber()
                );
            }
            if (!$user->getEmail() && $storage->getEmail()) {
                $user->setEmail($storage->getEmail());
                $this->currentUserProvider->getUserRepository()->updateEmail(
                    $user->getId(),
                    $storage->getEmail()
                );
            }
            if (!$storage->getAddressId()) {
                $needCreateAddress = true;
                $addressUserId = $storage->getUserId();
            }
        } else {
            $users = $this->currentUserProvider->getUserRepository()->findBy(
                ['LOGIC' => 'OR', ['=PERSONAL_PHONE' => $storage->getPhone()], ['=EMAIL' => $storage->getEmail()]]
            );

            $foundUser = null;
            /** @var User $user */
            foreach ($users as $user) {
                if ($user->getEmail() === $storage->getEmail()) {
                    $foundUser = $user;
                } elseif ($user->getPersonalPhone() === $storage->getPhone()) {
                    $foundUser = $user;
                }
            }

            if ($foundUser) {
                /** @noinspection PhpInternalEntityUsedInspection */
                $order->setFieldNoDemand('USER_ID', $foundUser->getId());
            } else {
                $password = randString(6);
                $user = (new User())
                    ->setName($storage->getName())
                    ->setEmail($storage->getEmail())
                    ->setLogin($storage->getPhone())
                    ->setPassword($password)
                    ->setPersonalPhone($storage->getPhone());
                $_SESSION['MANZANA_UPDATE'] = true;
                $_SESSION['SEND_REGISTER_EMAIL'] = true;
                $user = $this->userRegistrationProvider->register($user);

                /** @noinspection PhpInternalEntityUsedInspection */
                $order->setFieldNoDemand('USER_ID', $user->getId());
                $addressUserId = $user->getId();
                $needCreateAddress = true;
                $newUser = true;

                /* @todo вынести из сессии? */
                /* нужно для expertsender */
                /** пароль еще нужен для смс быстрого заказа */
                $_SESSION['NEW_USER'] = [
                    'LOGIN' => $storage->getPhone(),
                    'PASSWORD' => $password,
                ];
            }
        }

        /** @var PropertyValue $propertyValue */
        foreach ($order->getPropertyCollection() as $propertyValue) {
            $code = $propertyValue->getProperty()['CODE'];
            if ($code !== 'USER_REGISTERED') {
                continue;
            }
            $propertyValue->setValue(
                $newUser ? BitrixUtils::BX_BOOL_FALSE : BitrixUtils::BX_BOOL_TRUE
            );
            break;
        }

        /**
         * Сохраняем адрес, если:
         * 1) пользователь только что зарегистрирован
         * 2) авторизованный пользователь задал новый адрес
         */
        if (!$fastOrder &&
            $needCreateAddress &&
            $selectedDelivery &&
            $this->deliveryService->isDelivery($selectedDelivery)
        ) {
            $address = (new Address())
                ->setCity($storage->getCity())
                ->setCityLocation($storage->getCityCode())
                ->setUserId($addressUserId)
                ->setStreet($storage->getStreet())
                ->setHouse($storage->getHouse())
                ->setHousing($storage->getBuilding())
                ->setEntrance($storage->getPorch())
                ->setFloor($storage->getFloor())
                ->setFlat($storage->getApartment());

            try {
                $this->addressService->add($address);
            } catch (\Exception $e) {
                $this->log()->error(sprintf('failed to save address: %s', $e->getMessage()), [
                    'city' => $address->getCity(),
                    'location' => $address->getCityLocation(),
                    'userId' => $address->getUserId(),
                    'street' => $address->getStreet(),
                    'house' => $address->getHouse(),
                    'housing' => $address->getHousing(),
                    'entrance' => $address->getEntrance(),
                    'floor' => $address->getFloor(),
                    'flat' => $address->getFlat(),
                ]);
            }
        }

        try {
            $result = $order->save();
            if (!$result->isSuccess()) {
                throw new OrderCreateException(implode(', ', $result->getErrorMessages()));
            }
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to create order: %s', $e->getMessage()), [
                'fuserId' => $storage->getFuserId()
            ]);
            throw new OrderCreateException('failed to save order');
        }

        TaggedCacheHelper::clearManagedCache([
            'order:' . $order->getField('USER_ID'),
        ]);

        $this->orderStorageService->clearStorage($storage);
    }

    /**
     * Можно ли разделить заказ
     *
     * @param $storage
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws DeliveryNotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     * @throws UserMessageException
     * @return bool
     */
    public function canSplitOrder($storage): bool
    {
        $result = false;
        try {
            $delivery = $this->orderStorageService->getSelectedDelivery($storage);
            $stockResult = $delivery->getStockResult();
            $result = !$stockResult->getAvailable()->isEmpty() && !$stockResult->getDelayed()->isEmpty();
        } catch (NotFoundException $e) {
        }
        return $result;
    }

    /**
     * Разделение заказа на два.
     * В первом будут товары, которые есть в данный момент на складе / в магазине,
     * во втором - то, что необходимо привезти на склад / в магазин
     *
     * @param OrderStorage $storage
     *
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws DeliveryNotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws OrderCreateException
     * @throws OrderSplitException
     * @throws StoreNotFoundException
     * @throws UserMessageException
     * @throws ApplicationCreateException
     * @return OrderSplitResult[]
     */
    public function splitOrder(OrderStorage $storage): array
    {
        $updateBasket = function (Basket $basket, StockResultCollection $stockResultCollection) {
            /** @var BasketItem $basketItem */
            foreach ($basket as $i => $basketItem) {
                $found = false;
                /** @var StockResult $stockResultElement */
                foreach ($stockResultCollection as $stockResultElement) {
                    if ($stockResultElement->getOffer()->getId() === $basketItem->getProductId()) {
                        $found = true;
                        $basketItem->setField('QUANTITY', $stockResultElement->getAmount());
                        break;
                    }
                }

                if (!$found) {
                    $basket->deleteItem($i);
                }
            }
        };

        if (!$this->canSplitOrder($storage)) {
            throw new OrderSplitException('Cannot split order');
        }

        $storage1 = clone $storage;
        $storage2 = clone $storage;
        $storage2->setDeliveryInterval($storage->getSecondDeliveryInterval());
        $storage2->setDeliveryDate($storage->getSecondDeliveryDate());

        $basket = $this->basketService->getBasket();
        $delivery = $this->orderStorageService->getSelectedDelivery($storage);
        $available = $delivery->getStockResult()->getAvailable();
        $delayed = $delivery->getStockResult()->getDelayed();

        $basket1 = clone $basket;
        $updateBasket($basket1, $available);

        $basket2 = clone $basket;
        $updateBasket($basket2, $delayed);

        $order1 = $this->initOrder($storage1, $basket1);
        $order2 = $this->initOrder($storage2, $basket2);

        return [
            (new OrderSplitResult())->setOrderStorage($storage1)
                ->setOrder($order1),
            (new OrderSplitResult())->setOrderStorage($storage2)
                ->setOrder($order2),
        ];
    }

    /**
     * Инициализирует и сохраняет заказ.
     * Выполняет разделение заказов при необходимости
     *
     * @param OrderStorage $storage
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws DeliveryNotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderCreateException
     * @throws OrderSplitException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws UserMessageException
     * @return Order
     */
    public function createOrder(OrderStorage $storage): Order
    {
        /**
         * Разделение заказов
         */
        if ($storage->isSplit()) {
            $splitResults = $this->splitOrder($storage);
            $splitResult1 = array_shift($splitResults);
            $splitResult2 = array_shift($splitResults);

            $order = $splitResult1->getOrder();
            $order2 = $splitResult2->getOrder();

            $this->saveOrder($order, $splitResult1->getOrderStorage());
            /**
             * Если выбрано частичное получение, то второй заказ не создается
             */
            if (!$storage->isPartialGet()) {
                $this->saveOrder($order2, $splitResult2->getOrderStorage());
                $this->setOrderPropertyByCode($order2, 'RELATED_ORDER_ID', $order->getId());
                try {
                    $order2->save();
                } catch (\Exception $e) {
                    $this->log()->error('failed to set related order id', [
                        'order' => $order2->getId(),
                        'relatedOrder' => $order->getId()
                    ]);
                }
            }
        } else {
            $order = $this->initOrder($storage);
            $this->saveOrder($order, $storage);
        }

        return $order;
    }

    /**
     * @param Order $order
     *
     * @throws ObjectNotFoundException
     * @throws NotFoundException
     * @return Payment
     */
    public function getOrderPayment(Order $order): Payment
    {
        $payment = null;
        /** @var Payment $orderPayment */
        foreach ($order->getPaymentCollection() as $orderPayment) {
            if ($orderPayment->isInner()) {
                continue;
            }

            $payment = $orderPayment;
        }

        if (null === $payment) {
            throw new NotFoundException('payment system is not defined');
        }

        return $payment;
    }

    /**
     * @param Order $order
     * @return string
     * @throws ObjectNotFoundException
     */
    public function getOrderPaymentType(Order $order): string
    {
        return $this->getOrderPayment($order)->getPaySystem()->getField('CODE');
    }

    /**
     * @param Order $order
     * @param string $code
     *
     * @throws NotFoundException
     * @return PropertyValue
     */
    public function getOrderPropertyByCode(Order $order, string $code): PropertyValue
    {
        /** @var PropertyValue $propertyValue */
        foreach ($order->getPropertyCollection() as $propertyValue) {
            if ($propertyValue->getField('CODE') === $code) {
                return $propertyValue;
            }
        }

        throw new NotFoundException(sprintf('Свойство %s не найдено', $code));
    }

    /**
     * @param Order $order
     * @param string $code
     * @param $value
     */
    public function setOrderPropertyByCode(Order $order, string $code, $value): void
    {
        /** @var PropertyValue $propertyValue */
        foreach ($order->getPropertyCollection() as $propertyValue) {
            if ($propertyValue->getField('CODE') === $code) {
                $propertyValue->setValue($value);
            }
        }
    }

    /**
     * @param Order $order
     * @param array $codes
     *
     * @return array
     */
    public function getOrderPropertiesByCode(Order $order, array $codes): array
    {
        $result = [];
        /** @var PropertyValue $propertyValue */
        foreach ($order->getPropertyCollection() as $propertyValue) {
            $code = $propertyValue->getField('CODE');
            if (\in_array($propertyValue->getField('CODE'), $codes, true)) {
                $result[$code] = $propertyValue->getValue();
            }
        }

        return $result;
    }

    /**
     * @param Order $order
     *
     * @throws NotFoundException
     * @return string
     */
    public function getOrderDeliveryCode(Order $order): string
    {
        try {
            /** @var Shipment $shipment */
            foreach ($order->getShipmentCollection() as $shipment) {
                if ($shipment->isSystem()) {
                    continue;
                }

                return $this->deliveryService->getDeliveryCodeById($shipment->getDeliveryId());
            }
        } catch (DeliveryNotFoundException $e) {
        }

        throw new NotFoundException('Не указан тип доставки');
    }

    /**
     * @param Order $order
     *
     * @throws ArgumentException
     * @return string
     */
    public function getOrderDeliveryAddress(Order $order): string
    {
        $properties = $this->getOrderPropertiesByCode(
            $order,
            [
                'DPD_TERMINAL_CODE',
                'DELIVERY_PLACE_CODE',
                'CITY_CODE',
                'CITY',
                'STREET',
                'HOUSE',
                'BUILDING',
                'PORCH',
                'FLOOR',
                'APARTMENT',
            ]
        );
        $address = '';
        $deliveryCode = $this->getOrderDeliveryCode($order);
        switch (true) {
            case \in_array($deliveryCode, DeliveryService::PICKUP_CODES, true):
                if ($properties['DPD_TERMINAL_CODE'] && $properties['CITY_CODE']) {
                    $terminals = $this->deliveryService->getDpdTerminalsByLocation($properties['CITY_CODE']);
                    if ($terminal = $terminals[$properties['DPD_TERMINAL_CODE']]) {
                        $address = $terminal->getAddress();
                    }
                } elseif ($properties['DELIVERY_PLACE_CODE']) {
                    try {
                        $store = $this->storeService->getByXmlId($properties['DELIVERY_PLACE_CODE']);
                        $address = $store->getAddress();

                        if ($store->getMetro()) {
                            /** @noinspection PhpUnusedLocalVariableInspection */
                            list($services, $metro) = $this->storeService->getFullStoreInfo(new StoreCollection([$store]));

                            if ($metro[$store->getMetro()]) {
                                $address = 'м. ' . $metro[$store->getMetro()]['UF_NAME'] . ', ' . $address;
                            }
                        }
                    } catch (StoreNotFoundException $e) {
                    }
                }
                break;
            case \in_array($deliveryCode, DeliveryService::DELIVERY_CODES, true):
                if ($properties['CITY'] && $properties['STREET']) {
                    $address = [
                        $properties['CITY'],
                        $properties['STREET'],
                    ];
                    if (isset($properties['HOUSE'])) {
                        $address[] = $properties['HOUSE'];
                    }
                    if (isset($properties['BUILDING'])) {
                        $address[] = 'корпус ' . $properties['BUILDING'];
                    }
                    if (isset($properties['PORCH'])) {
                        $address[] = 'подъезд ' . $properties['PORCH'];
                    }
                    if (isset($properties['FLOOR'])) {
                        $address[] = 'этаж ' . $properties['FLOOR'];
                    }
                    if (isset($properties['APARTMENT'])) {
                        $address[] = 'кв. ' . $properties['APARTMENT'];
                    }
                    $address = implode(', ', $address);
                }
                break;
        }

        return $address;
    }

    /**
     * @param Order $order
     *
     * @throws NotFoundException
     * @return OfferCollection
     */
    public function getOrderProducts(Order $order): OfferCollection
    {
        $basket = $order->getBasket();
        $ids = [];
        /** @var BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            $ids[] = $basketItem->getProductId();
        }

        if (empty($ids)) {
            throw new NotFoundException('Basket is empty');
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return (new OfferQuery())->withFilterParameter('ID', $ids)->exec();
    }
}
