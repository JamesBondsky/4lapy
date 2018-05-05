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
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\NotFoundException as AddressNotFoundException;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DpdPickupResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Entity\DeliveryScheduleResult;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaPosService;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\LocationBundle\Entity\Address;
use FourPaws\LocationBundle\Exception\AddressSplitException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\SaleBundle\Entity\OrderSplitResult;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\DeliveryNotAvailableException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Exception\OrderSplitException;
use FourPaws\SapBundle\Consumer\ConsumerRegistry;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use FourPaws\UserBundle\Service\UserRegistrationProviderInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * Class OrderService
 *
 * @package FourPaws\SaleBundle\Service
 */
class OrderService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const PAYMENT_CASH_OR_CARD = 'cash-or-card';

    public const PAYMENT_CASH = 'cash';

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
     * Заказ в сборке
     */
    public const STATUS_IN_ASSEMBLY_1 = 'H';

    /**
     * Заказ в сборке
     */
    public const STATUS_IN_ASSEMBLY_2 = 'W';

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
     * @var LocationService
     */
    protected $locationService;

    /** @var ManzanaPosService */
    protected $manzanaPosService;

    /** @var ManzanaService */
    protected $manzanaService;

    /** @var array $paySystemServiceCache */
    private $paySystemServiceCache = [];

    /**
     * OrderService constructor.
     *
     * @param AddressService $addressService
     * @param BasketService $basketService
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param DeliveryService $deliveryService
     * @param LocationService $locationService
     * @param StoreService $storeService
     * @param OrderStorageService $orderStorageService
     * @param UserCitySelectInterface $userCityProvider
     * @param UserRegistrationProviderInterface $userRegistrationProvider
     * @param ManzanaPosService $manzanaPosService
     * @param ManzanaService $manzanaService
     */
    public function __construct(
        AddressService $addressService,
        BasketService $basketService,
        CurrentUserProviderInterface $currentUserProvider,
        DeliveryService $deliveryService,
        LocationService $locationService,
        StoreService $storeService,
        OrderStorageService $orderStorageService,
        UserCitySelectInterface $userCityProvider,
        UserRegistrationProviderInterface $userRegistrationProvider,
        ManzanaPosService $manzanaPosService,
        ManzanaService $manzanaService
    )
    {
        $this->addressService = $addressService;
        $this->basketService = $basketService;
        $this->currentUserProvider = $currentUserProvider;
        $this->deliveryService = $deliveryService;
        $this->storeService = $storeService;
        $this->orderStorageService = $orderStorageService;
        $this->userCityProvider = $userCityProvider;
        $this->userRegistrationProvider = $userRegistrationProvider;
        $this->locationService = $locationService;
        $this->manzanaPosService = $manzanaPosService;
        $this->manzanaService = $manzanaService;
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
        try {
            if (!$order = Order::load($id)) {
                throw new NotFoundException('');
            }

            if ($check) {
                if (!$hash && !$userId) {
                    throw new NotFoundException('');
                }
                if ($hash && $order->getHash() !== $hash) {
                    throw new NotFoundException('');
                }

                if ($userId && (int)$order->getUserId() !== $userId) {
                    throw new NotFoundException('');
                }
            }
        } catch (NotFoundException $e) {
            throw new NotFoundException(\sprintf(
                'Order #%s is not found',
                $id
            ));
        }

        return $order;
    }

    /**
     * @param OrderStorage                    $storage
     * @param Basket|null                     $basket
     * @param CalculationResultInterface|null $selectedDelivery
     * @param bool                            $fastOrder
     *
     * @throws \FourPaws\UserBundle\Exception\NotAuthorizedException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws BitrixProxyException
     * @throws DeliveryNotAvailableException
     * @throws DeliveryNotFoundException
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderCreateException
     * @throws StoreNotFoundException
     * @throws UserMessageException
     * @return Order
     */
    public function initOrder(
        OrderStorage $storage,
        ?Basket $basket = null,
        ?CalculationResultInterface $selectedDelivery = null,
        bool $fastOrder = false
    ): Order
    {
        $order = Order::create(SITE_ID);
        $selectedCity = $this->userCityProvider->getSelectedCity();

        $needRecalculateBasket = false;
        if (null === $basket) {
            $needRecalculateBasket = true;
            $basket = $this->basketService->getBasket();
        }

        if ($basket->getOrderableItems()->isEmpty()) {
            throw new OrderCreateException('Корзина пуста');
        }

        if (null === $selectedDelivery) {
            try {
                $selectedDelivery = $this->orderStorageService->getSelectedDelivery($storage);
            } catch (NotFoundException $e) {
                throw new DeliveryNotAvailableException('Нет доступных доставок');
            }
        }
        $selectedDelivery = clone $selectedDelivery;
        if (!$selectedDelivery->isSuccess()) {
            throw new DeliveryNotAvailableException('Нет доступных доставок');
        }

        /**
         * Привязываем корзину
         */
        if ($needRecalculateBasket) {
            $orderable = $selectedDelivery->getStockResult()->getOrderable();
            /** @var BasketItem $basketItem */
            foreach ($basket as $basketItem) {
                $toUpdate = [];
                $resultByOffer = $orderable->filterByOfferId($basketItem->getProductId());
                $diff = $basketItem->getQuantity() - $resultByOffer->getAmount();
                if ($resultByOffer->isEmpty()) {
                    $toUpdate['DELAY'] = BitrixUtils::BX_BOOL_TRUE;
                } elseif ($diff > 0) {
                    $toUpdate['QUANTITY'] = $resultByOffer->getAmount();
                    $this->basketService->addOfferToBasket(
                        $basketItem->getProductId(),
                        $diff,
                        [
                            'DELAY' => BitrixUtils::BX_BOOL_TRUE,
                            'PROPS' => [
                                [
                                    'NAME' => 'IS_TEMPORARY',
                                    'CODE' => 'IS_TEMPORARY',
                                    'VALUE' => 'Y',
                                ],
                            ],
                        ],
                        false,
                        $basket
                    );
                }

                if (!empty($toUpdate)) {
                    $basketItem->setFieldsNoDemand($toUpdate);
                }
            }
        }

        $order->setBasket($basket);
        if ($order->getBasket()->getOrderableItems()->isEmpty()) {
            throw new OrderCreateException('Корзина пуста');
        }

        /**
         * Задание способов доставки
         */
        $propertyValueCollection = $order->getPropertyCollection();
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
            foreach ($order->getBasket()->getOrderableItems() as $item) {
                $shipmentItem = $shipmentItemCollection->createItem($item);
                $shipmentItem->setQuantity($item->getQuantity());
            }

            $shipment->setFields(
                [
                    'DELIVERY_ID' => $selectedDelivery->getDeliveryId(),
                    'DELIVERY_NAME' => $selectedDelivery->getDeliveryName(),
                    'CURRENCY' => $order->getCurrency(),
                    'PRICE_DELIVERY' => $selectedDelivery->getPrice(),
                    'CUSTOM_PRICE_DELIVERY' => 'Y',
                ]
            );
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to set shipment fields: %s', $e->getMessage()), [
                'deliveryId' => $selectedDelivery->getDeliveryId(),
            ]);
            throw new OrderCreateException('Ошибка при создании отгрузки');
        }

        $shipmentCollection->calculateDelivery();

        $deliveryDate = $selectedDelivery->getDeliveryDate();

        /**
         * Задание свойств заказа, связанных с доставкой
         */
        /** @var PropertyValue $propertyValue */
        if (!$fastOrder) {
            foreach ($propertyValueCollection as $propertyValue) {
                $code = $propertyValue->getProperty()['CODE'];
                switch ($code) {
                    case 'DELIVERY_PLACE_CODE':
                        if ($this->deliveryService->isInnerPickup($selectedDelivery)) {
                            /** @var PickupResult $selectedDelivery */
                            $value = $storage->getDeliveryPlaceCode() ?: $selectedDelivery->getSelectedShop()->getXmlId();
                        } else {
                            $value = $selectedDelivery->getSelectedStore()->getXmlId();
                        }
                        break;
                    case 'DPD_TERMINAL_CODE':
                        if (!$this->deliveryService->isDpdPickup($selectedDelivery)) {
                            continue 2;
                        }
                        /** @var DpdPickupResult $selectedDelivery */
                        $value = $storage->getDeliveryPlaceCode() ?: $selectedDelivery->getSelectedShop()->getXmlId();
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
            $sum = $order->getBasket()->getOrderableItems()->getPrice() + $order->getDeliveryPrice();

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
                $this->log()->error(sprintf('bonus payment failed: %s', $e->getMessage()), [
                    'userId' => $storage->getUserId(),
                    'fuserId' => $storage->getFuserId(),
                ]);
                throw new OrderCreateException('Bonus payment failed');
            }

            try {
                $extPayment = $paymentCollection->createItem();
                $extPayment->setField('SUM', $sum);
                $extPayment->setField('PAY_SYSTEM_ID', $storage->getPaymentId());
                /** @var \Bitrix\Sale\PaySystem\Service $paySystem */
                $paySystem = $extPayment->getPaySystem();
                $extPayment->setField('PAY_SYSTEM_NAME', $paySystem->getField('NAME'));
            } catch (\Exception $e) {
                $this->log()->error(sprintf('order payment failed: %s', $e->getMessage()), [
                    'userId' => $storage->getUserId(),
                    'fuserId' => $storage->getFuserId(),
                ]);
                throw new OrderCreateException('Order payment failed');
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
            'FLOOR',
        ];
        $skipAddressProperties = !$this->deliveryService->isDelivery($selectedDelivery);

        /** @var PropertyValue $propertyValue */
        /** в быстром заказе здесь все сбрасывается нафиг */
        foreach ($propertyValueCollection as $propertyValue) {
            $code = $propertyValue->getProperty()['CODE'];
            if ($skipAddressProperties && \in_array($code, $addressProperties, true)) {
                continue;
            }

            $key = 'PROPERTY_' . $code;

            $value = $arrayStorage[$key] ?? null;

            if (null !== $value) {
                $propertyValue->setValue($value);
            }
        }

        /** установка свойств для быстрого заказа, здесь ибо сбрасывается выше */
        if($fastOrder) {
            $this->setOrderPropertyByCode($order, 'DELIVERY_INTERVAL', '');
            $this->setOrderPropertyByCode($order, 'SHIPMENT_PLACE_CODE', 'DC01');
            $this->setOrderPropertyByCode($order, 'CITY_CODE', $selectedCity['CODE']);
            $this->setOrderPropertyByCode($order, 'CITY', $selectedCity['NAME']);
        }
        return $order;
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param Order                           $order
     * @param OrderStorage                    $storage
     * @param CalculationResultInterface|null $selectedDelivery
     * @param bool                            $fastOrder
     *
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws \FourPaws\UserBundle\Exception\ValidationException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\UserBundle\Exception\NotAuthorizedException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws \FourPaws\UserBundle\Exception\BitrixRuntimeException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws DeliveryNotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderCreateException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws UserMessageException
     */
    public function saveOrder(
        Order $order,
        OrderStorage $storage,
        ?CalculationResultInterface $selectedDelivery = null,
        bool $fastOrder = false
    ): void
    {
        if (null === $selectedDelivery) {
            $selectedDelivery = $this->orderStorageService->getSelectedDelivery($storage);
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
        $canAttachCard = false;
        if ($storage->getUserId()) {
            $canAttachCard = true;
            /** @noinspection PhpInternalEntityUsedInspection */
            $order->setFieldNoDemand('USER_ID', $storage->getUserId());
            $user = $this->currentUserProvider->getCurrentUser();
            if (!$user->getEmail() && $storage->getEmail()) {
                $user->setEmail($storage->getEmail());
                $this->currentUserProvider->getUserRepository()->updateEmail(
                    $user->getId(),
                    $storage->getEmail()
                );
            }
            if (!$storage->getAddressId()) {
                $needCreateAddress = true;
            }
        } else {
            /** проверять надо на пустоту иначе  */
            if (!empty($storage->getEmail()) && !empty($storage->getPhone())) {
                $users = $this->currentUserProvider->getUserRepository()->findBy(
                    ['LOGIC' => 'OR', ['=PERSONAL_PHONE' => $storage->getPhone()], ['=EMAIL' => $storage->getEmail()]]
                );
            } elseif (!empty($storage->getEmail())) {
                $users = $this->currentUserProvider->getUserRepository()->findBy(['=EMAIL' => $storage->getEmail()]);
            } elseif (!empty($storage->getPhone())) {
                $users = $this->currentUserProvider->getUserRepository()->findBy(['=PERSONAL_PHONE' => $storage->getPhone()]);
            }

            $user = null;
            /** @var User $user */
            foreach ($users as $foundUser) {
                if (mb_strtolower($foundUser->getEmail()) === mb_strtolower($storage->getEmail())) {
                    $user = $foundUser;
                } elseif ($foundUser->getPersonalPhone() === $storage->getPhone()) {
                    $user = $foundUser;
                }
            }

            if ($user) {
                /** @noinspection PhpInternalEntityUsedInspection */
                $order->setFieldNoDemand('USER_ID', $user->getId());
            } else {
                $password = randString(6);
                $user = (new User())
                    ->setName($storage->getName())
                    ->setEmail($storage->getEmail())
                    ->setLogin($storage->getPhone())
                    ->setPassword($password)
                    ->setPersonalPhone($storage->getPhone());
                $_SESSION['SEND_REGISTER_EMAIL'] = true;
                $user = $this->userRegistrationProvider->register($user);

                /** @noinspection PhpInternalEntityUsedInspection */
                $order->setFieldNoDemand('USER_ID', $user->getId());
                $needCreateAddress = true;
                $newUser = true;

                /* @todo вынести из сессии? */
                /* нужно для expertsender */
                $_SESSION['NEW_USER'] = [
                    'LOGIN' => $storage->getPhone(),
                    'PASSWORD' => $password,
                ];

                $storage->setUserId($user->getId());
                $canAttachCard = true;
            }
        }

        $this->setOrderPropertyByCode(
            $order,
            'USER_REGISTERED',
            $newUser ? BitrixUtils::BX_BOOL_FALSE : BitrixUtils::BX_BOOL_TRUE
        );

        /**
         * Привязываем бонусную карту
         */
        if ($canAttachCard && !$user->getDiscountCardNumber() && $storage->getDiscountCardNumber()) {
            try {
                $existingContact = $this->manzanaService->getContactByUser($user);
                $contact = new Client();
                $contact->cardnumber = $this->manzanaService->prepareCardNumber($storage->getDiscountCardNumber());
                $contact->contactId = $existingContact->contactId;
                $this->manzanaService->updateContact($contact);
                $this->currentUserProvider->getUserRepository()->updateDiscountCard(
                    $user->getId(),
                    $this->manzanaService->prepareCardNumber($storage->getDiscountCardNumber())
                );
            } catch (ManzanaServiceException|ContactUpdateException $e) {
                $this->log()->error(
                    sprintf('failed to add bonus card to user: %s: %s', \get_class($e), $e->getMessage()),
                    ['userId' => $user->getId(), 'card' => $storage->getDiscountCardNumber()]
                );
            }
        }

        if (!$fastOrder) {
            /**
             * Сохраняем адрес, если:
             * 1) пользователь только что зарегистрирован
             * 2) авторизованный пользователь задал новый адрес
             */
            if ($needCreateAddress &&
                $this->deliveryService->isDelivery($selectedDelivery)
            ) {
                $address = $this->compileOrderAddress($order);
                $personalAddress = $this->addressService->createFromLocation($address)
                    ->setUserId($order->getUserId());
                try {
                    $this->addressService->add($personalAddress);
                    $storage->setAddressId($personalAddress->getId());
                } catch (\Exception $e) {
                    $this->log()->error(sprintf('failed to save address: %s', $e->getMessage()), [
                        'city' => $personalAddress->getCity(),
                        'location' => $personalAddress->getCityLocation(),
                        'userId' => $personalAddress->getUserId(),
                        'street' => $personalAddress->getStreet(),
                        'house' => $personalAddress->getHouse(),
                        'housing' => $personalAddress->getHousing(),
                        'entrance' => $personalAddress->getEntrance(),
                        'floor' => $personalAddress->getFloor(),
                        'flat' => $personalAddress->getFlat(),
                    ]);
                }
                /**
                 * Для самовывоза разбиваем адрес магазина и сохраняем в свойствах заказа
                 */
            } elseif ($this->deliveryService->isPickup($selectedDelivery)) {
                /** @var PickupResultInterface $selectedDelivery */
                $shop = $selectedDelivery->getSelectedShop();
                $addressString = $this->getOrderPropertyByCode($order, 'CITY')->getValue() . ', ' . $shop->getAddress();
                try {
                    $address = $this->locationService->splitAddress($addressString, $shop->getLocation());
                    $this->setOrderAddress($order, $address);
                } catch (AddressSplitException $e) {
                    $this->log()->error(sprintf('failed to save shop address: %s', $e->getMessage()), [
                        'fuserId' => $storage->getFuserId(),
                        'userId' => $storage->getUserId(),
                        'shop' => $shop->getXmlId()
                    ]);
                }
            }
        }

        $this->updateCommWayProperty($order, $selectedDelivery, $fastOrder);

        /**
         * Заполнение складов довоза товара для элементов корзины
         */
        if ($shipmentResults = $selectedDelivery->getShipmentResults()) {
            /** @var BasketItem $item */
            foreach ($order->getBasket()->getOrderableItems() as $item) {
                /** @var DeliveryScheduleResult $deliveryResult */
                if(!$deliveryResult = $shipmentResults->filterByOfferId($item->getProductId())->first()) {
                    continue;
                }

                $this->basketService->setBasketItemPropertyValue(
                    $item,
                    'SHIPMENT_PLACE_CODE',
                    $deliveryResult->getScheduleResult()->getSenderCode()
                );
            }
        }

        try {
            $result = $order->save();
            if (!$result->isSuccess()) {
                throw new OrderCreateException(implode(', ', $result->getErrorMessages()));
            }
        } catch (\Exception $e) {
            /** ошибка при создании заказа - удаляем ошибочный заказ, если он был создан */
            if ($order->getId() > 0) {
                Order::delete($order->getId());
            }
            $this->log()->error(sprintf('failed to create order: %s', $e->getMessage()), [
                'fuserId' => $storage->getFuserId(),
            ]);
            throw new OrderCreateException('failed to save order');
        }

        TaggedCacheHelper::clearManagedCache([
            'order:' . $order->getField('USER_ID'),
            'order:item:' . $order->getId(),
        ]);
    }

    /**
     * Разделение заказа на два.
     * В первом будут товары из регулярного ассортимента,
     * во втором - товары под заказ
     *
     * @param OrderStorage $storage
     *
     * @throws \FourPaws\UserBundle\Exception\NotAuthorizedException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws DeliveryNotAvailableException
     * @throws DeliveryNotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderCreateException
     * @throws OrderSplitException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws UserMessageException
     * @throws LoaderException
     * @throws BitrixProxyException
     * @return OrderSplitResult[]
     */
    public function splitOrder(OrderStorage $storage): array
    {
        $delivery = clone $this->orderStorageService->getSelectedDelivery($storage);
        if (!$this->orderStorageService->canSplitOrder($delivery)) {
            throw new OrderSplitException('Cannot split order');
        }

        $storage1 = clone $storage;
        $storage2 = clone $storage;
        $storage2->setDeliveryInterval($storage->getSecondDeliveryInterval());
        $storage2->setDeliveryDate($storage->getSecondDeliveryDate());
        $storage2->setComment($storage->getSecondComment());

        $basket = $this->basketService->getBasket();
        /** @noinspection PhpUnusedLocalVariableInspection */
        [$available, $delayed] = $this->orderStorageService->splitStockResult($delivery);

        try {
            /** @var Basket $basket1 */
            $basket1 = Basket::create(SITE_ID);
            $basket2 = Basket::create(SITE_ID);
            /** @var BasketItem $basketItem */
            foreach ($basket as $basketItem) {
                $availableAmount = $available->filterByOfferId($basketItem->getProductId())->getAmount();
                $delayedAmount = $delayed->filterByOfferId($basketItem->getProductId())->getAmount();

                if ($availableAmount) {
                    $this->basketService->addOfferToBasket(
                        $basketItem->getProductId(),
                        $availableAmount,
                        [],
                        false,
                        $basket1
                    );
                }
                if ($delayedAmount) {
                    $this->basketService->addOfferToBasket(
                        $basketItem->getProductId(),
                        $delayedAmount,
                        [],
                        false,
                        $basket2
                    );
                }
            }
        } catch (\Exception $e) {
            throw new OrderSplitException($e->getMessage());
        }

        $maxBonusesForOrder1 = floor(
            min($storage1->getBonus(), $basket1->getPrice() * BasketService::MAX_BONUS_PAYMENT)
        );
        if ($storage1->getBonus() > $maxBonusesForOrder1) {
            $storage1->setBonus($maxBonusesForOrder1);
            $storage2->setBonus(floor($storage1->getBonus() - $maxBonusesForOrder1));
        } else {
            $storage2->setBonus(0);
        }

        /**
         * Требуется пересчет стоимости доставки
         */
        $tmpDeliveries = $this->deliveryService->getByBasket(
            $basket1,
            '',
            [$delivery->getDeliveryCode()],
            $storage1->getCurrentDate()
        );
        if (!$tmpDelivery = reset($tmpDeliveries)) {
            throw new OrderSplitException('Cannot split order');
        }

        $order1 = $this->initOrder($storage1, $basket1, $tmpDelivery);
        $order2 = $this->initOrder($storage2, $basket2);

        /**
         * У второго заказа (содержащего товары под заказ) доставка бесплатная
         */
        try {
            /** @var Shipment $shipment */
            foreach ($order2->getShipmentCollection() as $shipment) {
                if ($shipment->isSystem()) {
                    continue;
                }
                $shipment->setField('PRICE_DELIVERY', 0);
            }
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to update shipment price: %s', $e->getMessage()), [
                'fuserId' => $storage->getFuserId(),
            ]);
        }

        return [
            (new OrderSplitResult())->setOrderStorage($storage1)
                ->setOrder($order1)
                ->setDelivery($tmpDelivery),
            (new OrderSplitResult())->setOrderStorage($storage2)
                ->setOrder($order2)
                ->setDelivery($delivery),
        ];
    }

    /**
     * Инициализирует и сохраняет заказ.
     * Выполняет разделение заказов при необходимости
     *
     * @param OrderStorage $storage
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\UserBundle\Exception\ValidationException
     * @throws \FourPaws\UserBundle\Exception\NotAuthorizedException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws \FourPaws\UserBundle\Exception\BitrixRuntimeException
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws BitrixProxyException
     * @throws DeliveryNotAvailableException
     * @throws DeliveryNotFoundException
     * @throws LoaderException
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
            [$splitResult1, $splitResult2] = $this->splitOrder($storage);

            $order = $splitResult1->getOrder();
            $storage1 = $splitResult1->getOrderStorage();
            $order2 = $splitResult2->getOrder();
            $storage2 = $splitResult2->getOrderStorage();

            $this->saveOrder($order, $storage1, $splitResult1->getDelivery());
            /**
             * Если выбрано частичное получение, то второй заказ не создается
             */
            $delivery = $this->orderStorageService->getSelectedDelivery($storage);
            if (!$this->orderStorageService->canGetPartial($delivery)) {
                /**
                 * чтобы предотвратить повторное создание адреса
                 */
                $storage2->setAddressId($storage1->getAddressId());

                $this->saveOrder($order2, $storage2, $splitResult2->getDelivery());
                $this->setOrderPropertyByCode($order2, 'RELATED_ORDER_ID', $order->getId());
                try {
                    $order2->save();
                } catch (\Exception $e) {
                    $this->log()->error('failed to set related order id', [
                        'order' => $order2->getId(),
                        'relatedOrder' => $order->getId(),
                    ]);
                }
                $this->setOrderPropertyByCode($order, 'RELATED_ORDER_ID', $order2->getId());
                try {
                    $order->save();
                } catch (\Exception $e) {
                    $this->log()->error('failed to set related order id', [
                        'order' => $order->getId(),
                        'relatedOrder' => $order2->getId(),
                    ]);
                }
            }

            $basket = $this->basketService->getBasket();
            $basket->clearCollection();
            $basket->save();
        } else {
            $order = $this->initOrder($storage);
            $this->saveOrder($order, $storage);
        }

        $this->orderStorageService->clearStorage($storage);

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
     *
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
     * @param        $value
     */
    public function setOrderPropertyByCode(Order $order, string $code, $value): void
    {
        /** @var PropertyValue $propertyValue */
        foreach ($order->getPropertyCollection() as $propertyValue) {
            if ($propertyValue->getField('CODE') === $code) {
                $propertyValue->setValue($value);
                break;
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
                $result[$code] = (string)$propertyValue->getValue();
            }
        }

        return $result;
    }

    /**
     * @param Order $order
     * @param array $properties
     *
     * @return Order
     */
    public function setOrderPropertiesByCode(Order $order, array $properties): Order
    {
        /** @var PropertyValue $propertyValue */
        foreach ($order->getPropertyCollection() as $propertyValue) {
            $code = $propertyValue->getField('CODE');
            if (isset($properties[$code])) {
                $propertyValue->setValue($properties[$code]);
            }
        }

        return $order;
    }

    /**
     * @param Order $order
     *
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
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
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws \Exception
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
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
                        $store = $this->storeService->getStoreByXmlId($properties['DELIVERY_PLACE_CODE']);
                        $address = $store->getAddress();

                        if ($store->getMetro()) {
                            /** @noinspection PhpUnusedLocalVariableInspection */
                            [$services, $metro] = $this->storeService->getFullStoreInfo(new StoreCollection([$store]));

                            if ($metro[$store->getMetro()]) {
                                $address = 'м. ' . $metro[$store->getMetro()]['UF_NAME'] . ', ' . $address;
                            }
                        }
                    } catch (StoreNotFoundException $e) {
                    }
                }
                break;
            case \in_array($deliveryCode, DeliveryService::DELIVERY_CODES, true):
                $address = (string)$this->compileOrderAddress($order);
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

    /**
     * @param Order $order
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function processPaymentError(Order $order): void
    {
        /** @todo костыль */
        if (!$payment = PaySystemActionTable::getList(['filter' => ['CODE' => static::PAYMENT_CASH]])->fetch()) {
            $this->log()->error('cash payment not found');
            return;
        }
        $paySystemId = $payment['ID'];
        $sapConsumer = Application::getInstance()->getContainer()->get(ConsumerRegistry::class);
        $updateOrder = function (Order $order) use ($paySystemId, $sapConsumer) {
            try {
                $payment = $this->getOrderPayment($order);
                if ($payment->isPaid() ||
                    $payment->getPaySystem()->getField('CODE') !== OrderService::PAYMENT_ONLINE
                ) {
                    return;
                }
                $newPayment = $order->getPaymentCollection()->createItem();
                $newPayment->setField('SUM', $payment->getSum());
                $newPayment->setField('PAY_SYSTEM_ID', $paySystemId);
                $paySystem = $newPayment->getPaySystem();
                $newPayment->setField('PAY_SYSTEM_NAME', $paySystem->getField('NAME'));
                $payment->delete();
                $newPayment->save();
                $commWay = $this->getOrderPropertyByCode($order, 'COM_WAY');
                $commWay->setValue(OrderPropertyService::COMMUNICATION_PAYMENT_ANALYSIS);
                $order->save();
                $sapConsumer->consume($order);
            } catch (\Exception $e) {
                $this->log()->error(sprintf('failed to process payment error: %s', $e->getMessage()), [
                    'order' => $order->getId(),
                ]);
            }
        };
        $updateOrder($order);
        if ($this->hasRelatedOrder($order)) {
            $relatedOrder = $this->getRelatedOrder($order);
            if (!$relatedOrder->isPaid()) {
                $updateOrder($relatedOrder);
            }
        }
    }

    public function hasRelatedOrder(Order $order): bool
    {
        return (int)$this->getOrderPropertyByCode($order, 'RELATED_ORDER_ID')->getValue() > 0;
    }

    /**
     * @param Order $order
     *
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws NotFoundException
     * @return Order
     */
    public function getRelatedOrder(Order $order): Order
    {
        $relatedOrder = Order::load(
            (int)$this->getOrderPropertyByCode($order, 'RELATED_ORDER_ID')->getValue()
        );

        if (!$relatedOrder instanceof Order) {
            throw new NotFoundException(sprintf('Related order for order %s not found', $order->getId()));
        }

        return $relatedOrder;
    }

    /**
     * @param Order                      $order
     * @param CalculationResultInterface $delivery
     * @param bool                       $isFastOrder
     *
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws ArgumentException
     * @throws DeliveryNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function updateCommWayProperty(
        Order $order,
        CalculationResultInterface $delivery,
        bool $isFastOrder = false
    ): void
    {
        $commWay = $this->getOrderPropertyByCode($order, 'COM_WAY');
        $value = $commWay->getValue();
        $changed = false;

        $deliveryFromShop = $this->deliveryService->isInnerDelivery($delivery) && $delivery->getSelectedStore()->isShop();
        $stockResult = $delivery->getStockResult();
        if (!$isFastOrder) {
            /**
             * Если у заказа самовывоз из магазина или курьерская доставка из зоны 2,
             * и в наличии более 90% от суммы заказа, при этом имеются отложенные товары,
             * то способ коммуникации изменяется на "Телефонный звонок (анализ)"
             */
            if (($this->deliveryService->isInnerPickup($delivery) || $deliveryFromShop) &&
                !$stockResult->getDelayed()->isEmpty()
            ) {
                $totalPrice = $order->getBasket()->getPrice();
                $availablePrice = $stockResult->getAvailable()->getPrice();
                if ($availablePrice > $totalPrice * 0.9) {
                    $value = OrderPropertyService::COMMUNICATION_PHONE_ANALYSIS;
                    $changed = true;
                }
            }
        }

        if (!$changed) {
            switch (true) {
                case $isFastOrder:
                    $value = OrderPropertyService::COMMUNICATION_ONE_CLICK;
                    break;
                case $this->isSubscribe($order):
                    $value = OrderPropertyService::COMMUNICATION_SUBSCRIBE;
                    break;
                case !$this->validateAddress($order):
                    $value = OrderPropertyService::COMMUNICATION_ADDRESS_ANALYSIS;
                    break;
                // способ получения 07
                case $this->deliveryService->isDpdPickup($delivery):
                case $this->deliveryService->isDpdDelivery($delivery):
                    $value = OrderPropertyService::COMMUNICATION_PHONE;
                    break;
                // способ получения 04
                case $this->deliveryService->isInnerPickup($delivery) && $stockResult->getDelayed()->isEmpty():
                    // способ получения 06
                case $deliveryFromShop && $stockResult->getDelayed()->isEmpty():
                    $value = OrderPropertyService::COMMUNICATION_SMS;
                    break;
            }
        }

        $commWay->setValue($value);
    }

    /**
     * @param Order $order
     *
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @return bool
     */
    protected function validateAddress(Order $order): bool
    {
        $result = true;
        if (\in_array($this->getOrderDeliveryCode($order), $this->deliveryService::DELIVERY_CODES, true)) {
            $address = $this->compileOrderAddress($order);

            $result = $this->locationService->validateAddress($address);
        }

        return $result;
    }

    /**
     * @param Order $order
     *
     * @return Address
     */
    protected function compileOrderAddress(Order $order): Address
    {
        $properties = $this->getOrderPropertiesByCode($order, [
            'CITY_CODE',
            'CITY',
            'STREET',
            'HOUSE',
            'BUILDING',
            'PORCH',
            'FLOOR',
            'APARTMENT',
        ]);

        $address = (new Address())
            ->setCity($properties['CITY'])
            ->setLocation($properties['CITY_CODE'])
            ->setStreet($properties['STREET'])
            ->setHouse($properties['HOUSE'])
            ->setHousing($properties['BUILDING'])
            ->setEntrance($properties['PORCH'])
            ->setFloor($properties['FLOOR'])
            ->setFlat($properties['APARTMENT']);

        return $address;
    }

    /**
     * @param Order $order
     * @param Address $address
     *
     * @return Order
     */
    protected function setOrderAddress(Order $order, Address $address): Order
    {
        $properties = [
            'CITY_CODE' => $address->getLocation(),
            'CITY' => $address->getCity(),
            'STREET' => $address->getStreet(),
            'HOUSE' => $address->getHouse(),
            'BUILDING' => $address->getHousing(),
            'PORCH' => $address->getEntrance(),
            'FLOOR' => $address->getFloor(),
            'APARTMENT' => $address->getFlat()
        ];

        return $this->setOrderPropertiesByCode($order, $properties);
    }

    /**
     * Бонусы, начисленные за заказ
     *
     * @param Order $order
     * @param User  $user
     *
     * @return string
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws \RuntimeException
     */
    public function getOrderBonusSum(Order $order, ?User $user = null): string
    {
        $propertyValue = $this->getOrderPropertyByCode($order, 'BONUS_COUNT');

        if (!$user) {
            $user = $this->currentUserProvider->getUserRepository()->find($order->getUserId());
        }

        if (null === $propertyValue->getValue()) {
            try {
                $propertyValue->setValue(0);
                if ($user->getDiscountCardNumber()) {
                    /**
                     * У юзера есть бонусная карта, а бонусы за заказ еще не начислены.
                     */
                    $cheque = $this->manzanaPosService->processChequeWithoutBonus(
                        $this->manzanaPosService->buildRequestFromBasket(
                            $order->getBasket(),
                            $user->getDiscountCardNumber()
                        )
                    );
                    $propertyValue->setValue($cheque->getChargedBonus());
                }
                $order->save();
            } catch (ExecuteException $e) {
                $this->log()->error(sprintf('failed to get charged bonus: %s', $e->getMessage()), [
                    'orderId' => $order->getId(),
                ]);
            } catch (\Exception $e) {
                $this->log()->error(sprintf('failed to set charged bonus for order: %s', $e->getMessage()), [
                    'orderId' => $order->getId(),
                    'bonus' => $propertyValue->getValue(),
                ]);
            }
        }

        return $propertyValue->getValue();
    }

    /**
     * @param Order $order
     *
     * @return bool
     * @throws ObjectNotFoundException
     */
    public function isOnlinePayment(Order $order): bool
    {
        $result = false;
        try{
            $paymentCode = $this->getOrderPaymentType($order);
            if ($paymentCode === static::PAYMENT_ONLINE) {
                $result = true;
            }
        } catch(NotFoundException $e){}

        return $result;
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function isSubscribe(Order $order): bool
    {
        try {
            $propValue = $this->getOrderPropertyByCode($order, 'IS_SUBSCRIBE');
            $result = $propValue->getValue() === 'Y';
        } catch (\Exception $exception) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    public function isManzanaOrder(Order $order): bool
    {
        try {
            $propValue = $this->getOrderPropertyByCode($order, 'MANZANA_NUMBER');
            $result = !empty($propValue);
        } catch (\Exception $exception) {
            $result = false;
        }

        return $result;
    }

    /**
     * @return null|\Bitrix\Sale\PaySystem\Service
     */
    public function getCashPaySystemService(): ?\Bitrix\Sale\PaySystem\Service
    {
        $paySystemService = null;
        if (!isset($this->paySystemServiceCache['cash'])) {
            $this->paySystemServiceCache['cash'] = null;
            $data = \Bitrix\Sale\PaySystem\Manager::getByCode(static::PAYMENT_CASH_OR_CARD);
            if ($data) {
                $this->paySystemServiceCache['cash'] = new \Bitrix\Sale\PaySystem\Service(
                    $data
                );
            }
        }

        if ($this->paySystemServiceCache['cash']) {
            /** @var \Bitrix\Sale\PaySystem\Service $paySystemService */
            $paySystemService = clone $this->paySystemServiceCache['cash'];
        }

        return $paySystemService;
    }

    /**
     * @param Order $order
     * @return \Bitrix\Main\Type\Date|null
     */
    public function getOrderDeliveryDate(Order $order): ?\Bitrix\Main\Type\Date
    {
        $deliveryDate = null;
        try {
            $propValue = $this->getOrderPropertyByCode($order, 'DELIVERY_DATE');
            $value = $propValue->getValue();
            if ($value instanceof \Bitrix\Main\Type\Date) {
                $deliveryDate = $value;
            }
        } catch (\Exception $exception) {
            // просто вернем null
        }

        return $deliveryDate;
    }

}
