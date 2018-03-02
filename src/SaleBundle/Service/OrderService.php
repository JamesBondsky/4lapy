<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentCollection;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundEXception;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\PersonalBundle\Exception\NotFoundException as AddressNotFoundException;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\FastOrderCreateException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use FourPaws\UserBundle\Service\UserRegistrationProviderInterface;

class OrderService
{
    const PAYMENT_CASH = 'cash';

    const PAYMENT_CARD = 'card';

    const PAYMENT_ONLINE = 'card-online';

    const PAYMENT_INNER = 'inner';

    const PROPERTY_TYPE_ENUM = 'ENUM';

    /**
     * Дефолтный статус заказа при курьерской доставке
     */
    const STATUS_NEW_COURIER = 'Q';

    /**
     * Дефолтный статус заказа при самовывозе
     */
    const STATUS_NEW_PICKUP = 'N';

    /**
     * Заказ доставляется ("Исполнен" для курьерской доставки)
     */
    const STATUS_DELIVERING = 'Y';

    /**
     * Заказ в пункте выдачи
     */
    const STATUS_ISSUING_POINT = 'F';

    /**
     * Заказ доставлен
     */
    const STATUS_DELIVERED = 'J';

    /**
     * 90% заказа можно оплатить бонусами
     */
    const MAX_BONUS_PAYMENT = 0.9;

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
     * @var BaseResult[]
     */
    protected $deliveries;

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
     * @param bool $save
     * @param bool $fastOrder
     *
     * @throws \FourPaws\SaleBundle\Exception\FastOrderCreateException
     * @throws \Exception
     * @throws OrderCreateException
     * @throws NotFoundException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentTypeException
     * @throws BitrixRuntimeException
     * @throws ValidationException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @return Order
     */
    public function createOrder(OrderStorage $storage, $save = true, bool $fastOrder = false): Order
    {
        $order = Order::create(SITE_ID);
        $selectedCity = $this->userCityProvider->getSelectedCity();

        /**
         * Привязываем корзину
         */
        /** @noinspection PhpParamsInspection */
        $order->setBasket($this->basketService->getBasket());
        if ($order->getBasket()->getOrderableItems()->isEmpty()) {
            throw new OrderCreateException('Корзина пуста');
        }

        /**
         * Задание способов оплаты
         */
        if ($storage->getPaymentId()) {
            $paymentCollection = $order->getPaymentCollection();
            $sum = $order->getBasket()->getOrderableItems()->getPrice();

            if ($storage->getBonus()) {
                $innerPayment = $paymentCollection->getInnerPayment();
                $innerPayment->setField('SUM', $storage->getBonus());
                $sum -= $storage->getBonus();
            }

            $extPayment = $paymentCollection->createItem();
            $extPayment->setField('SUM', $sum);
            $extPayment->setField('PAY_SYSTEM_ID', $storage->getPaymentId());

            /** @var \Bitrix\Sale\PaySystem\Service $paySystem */
            $paySystem = $extPayment->getPaySystem();
            $extPayment->setField('PAY_SYSTEM_NAME', $paySystem->getField('NAME'));
        } elseif ($save) {
            if (!$fastOrder) {
                throw new OrderCreateException('Не выбран способ оплаты');
            }
        }

        $deliveries = $this->getDeliveries();
        $selectedDelivery = null;
        if ($fastOrder) {
            /** устанавливаем самовывоз для быстрого заказа */
            if (!empty($deliveries)) {
                $selectedDelivery = current($deliveries);
            } else {
                throw new FastOrderCreateException(
                    'Оформление быстрого заказа невозможно, пожалуйста обратить к администратору или попробуйте полный процесс оформления'
                );
            }
        } else {
            /** @var BaseResult $delivery */
            foreach ($deliveries as $delivery) {
                if ($storage->getDeliveryId() === $delivery->getDeliveryId()) {
                    $selectedDelivery = clone $delivery;
                    if ($this->deliveryService->isPickup($selectedDelivery)) {
                        $selectedDelivery->setStockResult(
                            $selectedDelivery->getStockResult()->filterByStore(
                                $this->storeService->getByXmlId($storage->getDeliveryPlaceCode())
                            )
                        );
                    }
                    break;
                }
            }
        }

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

            if (null === $selectedDelivery) {
                throw new OrderCreateException('Не выбрана доставка');
            }

            if ($this->deliveryService->isDelivery($selectedDelivery)) {
                $order->setFieldNoDemand('STATUS_ID', static::STATUS_NEW_COURIER);
            }

            $shipment->setFields(
                [
                    'DELIVERY_ID'           => $selectedDelivery->getDeliveryId(),
                    'DELIVERY_NAME'         => $selectedDelivery->getDeliveryName(),
                    'CURRENCY'              => $order->getCurrency(),
                    'PRICE_DELIVERY'        => $selectedDelivery->getPrice(),
                    'CUSTOM_PRICE_DELIVERY' => 'Y',
                ]
            );

            $shipmentCollection->calculateDelivery();

            $deliveryDate = $selectedDelivery->getDeliveryDate();

            /**
             * Задание свойств заказа, связанных с доставкой
             */
            /** @var PropertyValue $propertyValue */
            foreach ($propertyValueCollection as $propertyValue) {
                $code = $propertyValue->getProperty()['CODE'];
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

                            /** @var Interval $interval */
                            if (!$interval = $selectedDelivery->getIntervals()[$index]) {
                                continue 2;
                            }

                            $value = sprintf(
                                '%s:00-%s:00',
                                str_pad($interval->getFrom(), 2, '0', STR_PAD_LEFT),
                                str_pad($interval->getTo(), 2, '0', STR_PAD_LEFT)
                            );
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
        } elseif ($save) {
            if (!$fastOrder) {
                throw new OrderCreateException('Не выбрана доставка');
            }
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
        $arrayStorage = $this->orderStorageService->storageToArray($storage);

        /** @var PropertyValue $propertyValue */
        foreach ($propertyValueCollection as $propertyValue) {
            $code = $propertyValue->getProperty()['CODE'];
            $key = 'PROPERTY_' . $code;

            if (!empty($arrayStorage[$key])) {
                $propertyValue->setValue($arrayStorage[$key]);
            }
        }

        $order->doFinalAction(true);

        if ($save) {
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
                    $password = randString(6);
                    $user = (new User())
                        ->setName($storage->getName())
                        ->setEmail($storage->getEmail())
                        ->setLogin($storage->getPhone())
                        ->setPassword($password)
                        ->setPersonalPhone($storage->getPhone());
                    $_SESSION['MANZANA_UPDATE'] = true;
                    $user = $this->userRegistrationProvider->register($user);

                    $order->setFieldNoDemand('USER_ID', $user->getId());
                    $addressUserId = $user->getId();
                    $needCreateAddress = true;
                    $newUser = true;

                    /* @todo вынести из сессии? */
                    /* нужно для expertsender */
                    /** пароль еще нужен для смс быстрого заказа */
                    $_SESSION['NEW_USER'] = [
                        'LOGIN'    => $storage->getPhone(),
                        'PASSWORD' => $password,
                    ];
                }
            }

            /** @var PropertyValue $propertyValue */
            foreach ($propertyValueCollection as $propertyValue) {
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
            if ($needCreateAddress &&
                $selectedDelivery &&
                $this->deliveryService->isDelivery($selectedDelivery) &&
                !$fastOrder
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

                $this->addressService->add($address);
            }

            $result = $order->save();
            if (!$result->isSuccess()) {
                throw new OrderCreateException(implode(', ', $result->getErrorMessages()));
            }

            $this->orderStorageService->clearStorage($storage);
        }

        return $order;
    }

    /**
     * @param bool $reload
     *
     * @return BaseResult[]
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
     * @param Order $order
     *
     * @return Payment
     * @throws NotFoundException
     */
    public function getOnlinePayment(Order $order): Payment
    {
        /** @var Payment $orderPayment */
        foreach ($order->getPaymentCollection() as $orderPayment) {
            if ($orderPayment->isInner()) {
                continue;
            }

            if ($orderPayment->getPaySystem()->getField('CODE') === static::PAYMENT_ONLINE) {
                return $orderPayment;
            }
        }

        throw new NotFoundException('В данном заказе нет онлайн-оплаты');
    }

    /**
     * @param Order $order
     * @param string $code
     *
     * @return PropertyValue
     * @throws NotFoundException
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
    public function setOrderPropertyByCode(Order $order, string $code, $value)
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
     * @return string
     * @throws NotFoundException
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
        if ($properties['DELIVERY_PLACE_CODE']) {
            try {
                $store = $this->storeService->getByXmlId($properties['DELIVERY_PLACE_CODE']);
                $address = $store->getAddress();

                if ($store->getMetro()) {
                    /** @noinspection PhpUnusedLocalVariableInspection */
                    list ($services, $metro) = $this->storeService->getFullStoreInfo(new StoreCollection([$store]));

                    if ($metro[$store->getMetro()]) {
                        $address = 'м. ' . $metro[$store->getMetro()]['UF_NAME'] . ', ' . $address;
                    }
                }
            } catch (StoreNotFoundException $e) {
            }
        } elseif ($properties['DELIVERY_PLACE_CODE'] && $properties['CITY_CODE']) {
            $terminals = $this->deliveryService->getDpdTerminalsByLocation($properties['CITY_CODE']);
            if ($terminal = $terminals[$properties['DPD_TERMINAL_CODE']]) {
                $address = $terminal->getAddress();
            }
        } elseif ($properties['CITY'] && $properties['STREET']) {
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

        return $address;
    }

    /**
     * @param Order $order
     *
     * @return OfferCollection
     * @throws NotFoundException
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
            throw new NotFoundException('Корзина заказа пуста');
        }

        return (new OfferQuery())->withFilterParameter('ID', $ids)->exec();
    }
}
