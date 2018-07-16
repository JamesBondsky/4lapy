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
use Bitrix\Main\Type\Date;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketPropertyItem;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem\Service;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\UserMessageException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\NotFoundException as AddressNotFoundException;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DpdPickupResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Entity\DeliveryScheduleResult;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\ContactNotFoundException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\Manzana\Exception\ManzanaException;
use FourPaws\External\Manzana\Model\Card;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaPosService;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\LocationBundle\Entity\Address;
use FourPaws\LocationBundle\Exception\AddressSplitException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Enum\OrderStatus;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\DeliveryNotAvailableException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Exception\OrderSplitException;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponStorageInterface;
use FourPaws\SapBundle\Consumer\ConsumerRegistry;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\NotFoundException as UserNotFoundException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAvatarAuthorizationInterface;
use FourPaws\UserBundle\Service\UserRegistrationProviderInterface;
use FourPaws\UserBundle\Service\UserSearchInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class OrderService
 *
 * @package FourPaws\SaleBundle\Service
 */
class OrderService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const PROPERTY_TYPE_ENUM = 'ENUM';

    /**
     * @var AddressService
     */
    protected $addressService;

    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    /**
     * @var UserSearchInterface
     */
    protected $userProvider;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var OrderStorageService
     */
    protected $orderStorageService;

    /**
     * @var OrderSplitService
     */
    protected $orderSplitService;

    /**
     * @var UserRegistrationProviderInterface
     */
    protected $userRegistrationProvider;

    /**
     * @var UserAvatarAuthorizationInterface
     */
    protected $userAvatarAuthorization;

    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * @var LocationService
     */
    protected $locationService;

    /**
     * @var ManzanaPosService
     */
    protected $manzanaPosService;

    /**
     * @var ManzanaService
     */
    protected $manzanaService;

    /**
     * @var CouponStorageInterface
     */
    protected $couponStorage;

    /** @var array $paySystemServiceCache */
    private $paySystemServiceCache = [];

    /**
     * OrderService constructor.
     *
     * @param AddressService                    $addressService
     * @param BasketService                     $basketService
     * @param PaymentService                    $paymentService
     * @param CurrentUserProviderInterface      $currentUserProvider
     * @param UserSearchInterface               $userProvider
     * @param DeliveryService                   $deliveryService
     * @param LocationService                   $locationService
     * @param StoreService                      $storeService
     * @param OrderStorageService               $orderStorageService
     * @param OrderSplitService                 $orderSplitService
     * @param UserAvatarAuthorizationInterface  $userAvatarAuthorization
     * @param UserRegistrationProviderInterface $userRegistrationProvider
     * @param ManzanaPosService                 $manzanaPosService
     * @param ManzanaService                    $manzanaService
     * @param CouponStorageInterface            $couponStorage
     */
    public function __construct(
        AddressService $addressService,
        BasketService $basketService,
        PaymentService $paymentService,
        CurrentUserProviderInterface $currentUserProvider,
        UserSearchInterface $userProvider,
        DeliveryService $deliveryService,
        LocationService $locationService,
        StoreService $storeService,
        OrderStorageService $orderStorageService,
        OrderSplitService $orderSplitService,
        UserAvatarAuthorizationInterface $userAvatarAuthorization,
        UserRegistrationProviderInterface $userRegistrationProvider,
        ManzanaPosService $manzanaPosService,
        ManzanaService $manzanaService,
        CouponStorageInterface $couponStorage
    ) {
        $this->addressService = $addressService;
        $this->basketService = $basketService;
        $this->paymentService = $paymentService;
        $this->currentUserProvider = $currentUserProvider;
        $this->userProvider = $userProvider;
        $this->deliveryService = $deliveryService;
        $this->storeService = $storeService;
        $this->orderStorageService = $orderStorageService;
        $this->orderSplitService = $orderSplitService;
        $this->userAvatarAuthorization = $userAvatarAuthorization;
        $this->userRegistrationProvider = $userRegistrationProvider;
        $this->locationService = $locationService;
        $this->manzanaPosService = $manzanaPosService;
        $this->manzanaService = $manzanaService;
        $this->couponStorage = $couponStorage;

    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Получение заказа по id
     *
     * @param int    $id     id заказа
     * @param bool   $check  выполнять ли проверки
     * @param int    $userId id пользователя, к которому привязан заказ
     * @param string $hash   хеш заказа (проверяется, если не передан userId)
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
     *
     * @return Order
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
     * @throws StoreNotFoundException
     * @throws UserMessageException
     */
    public function initOrder(
        OrderStorage $storage,
        ?Basket $basket = null,
        ?CalculationResultInterface $selectedDelivery = null
    ): Order {
        $order = Order::create(SITE_ID);

        $checkAvailability = false;
        if (null === $basket) {
            $checkAvailability = true;
            $basket = $this->basketService->getBasket();
        }

        if ($basket->getOrderableItems()->isEmpty()) {
            throw new OrderCreateException('Basket is empty');
        }

        if (null === $selectedDelivery) {
            try {
                $selectedDelivery = $this->orderStorageService->getSelectedDelivery($storage);
            } catch (NotFoundException $e) {
                $this->log()->error('No available deliveries', [
                    'fuserId' => $storage->getFuserId(),
                    'userId' => $storage->getUserId(),
                    'location' => $storage->getCityCode(),
                    'basket' => $this->basketService->getBasketProducts($basket)
                ]);

                throw new DeliveryNotAvailableException('No available deliveries');
            }
        }

        $selectedDelivery = clone $selectedDelivery;
        if (!$selectedDelivery->isSuccess()) {
            $this->log()->error('Selected delivery is not available', [
                'fuserId' => $storage->getFuserId(),
                'userId' => $storage->getUserId(),
                'location' => $storage->getCityCode(),
                'basket' => $this->basketService->getBasketProducts($basket)
            ]);

            throw new DeliveryNotAvailableException('Selected delivery is not available');
        }

        if ($isDiscountEnabled = Manager::isExtendDiscountEnabled()) {
            Manager::disableExtendsDiscount();
        }

        /**
         * Привязываем корзину
         */
        if ($checkAvailability) {
            $orderable = $selectedDelivery->getStockResult()->getOrderable();
            /** @var BasketItem $basketItem */
            foreach ($basket as $basketItem) {
                $toUpdate = [
                    'CUSTOM_PRICE' => 'Y',
                ];

                $amount = 0;
                $resultsByOffer = $orderable->filterByOfferId($basketItem->getProductId());
                if (!$resultsByOffer->isEmpty()) {
                    foreach ($resultsByOffer as $resultByOffer) {
                        if ($priceForAmount = $resultByOffer->getPriceForAmountByBasketCode((string)$basketItem->getBasketCode())) {
                            $amount += $priceForAmount->getAmount();
                        }
                    }
                }
                $diff = $basketItem->getQuantity() - $amount;
                if ($amount === 0) {
                    $toUpdate['DELAY'] = BitrixUtils::BX_BOOL_TRUE;
                } elseif ($diff > 0) {
                    $toUpdate['QUANTITY'] = $resultByOffer->getAmount();

                    $this->basketService->addOfferToBasket(
                        $basketItem->getProductId(),
                        $diff,
                        [
                            'CUSTOM_PRICE' => 'Y',
                            'DELAY' => BitrixUtils::BX_BOOL_TRUE,
                            'PROPS' => $basketItem->getPropertyCollection()->getPropertyValues(),
                        ],
                        false,
                        $basket
                    );
                }

                if (!empty($toUpdate)) {
                    $basketItem->setFields($toUpdate);
                }
            }
        }

        $order->setBasket($basket->getOrderableItems());
        if ($order->getBasket()->isEmpty()) {
            throw new OrderCreateException('Basket is empty');
        }

        /**
         * Задание способов доставки
         */
        $propertyValueCollection = $order->getPropertyCollection();
        $locationProp = $order->getPropertyCollection()->getDeliveryLocation();
        if (!$locationProp) {
            $this->log()->critical('Order location property is not defined');
            throw new OrderCreateException('Order location property is not defined');
        }
        $locationProp->setValue($storage->getCityCode());

        if ($this->deliveryService->isDelivery($selectedDelivery)) {
            /** @var DeliveryResultInterface $selectedDelivery */
            $selectedDelivery->setDateOffset($storage->getDeliveryDate());
            if (($intervalIndex = $storage->getDeliveryInterval() - 1) >= 0) {
                /** @var Interval $interval */
                if ($interval = $selectedDelivery->getAvailableIntervals()[$intervalIndex]) {
                    $selectedDelivery->setSelectedInterval($interval);
                }
            }
            /** @noinspection PhpInternalEntityUsedInspection */
            $order->setFieldNoDemand('STATUS_ID', OrderStatus::STATUS_NEW_COURIER);
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
                    'DELIVERY_ID'           => $selectedDelivery->getDeliveryId(),
                    'DELIVERY_NAME'         => $selectedDelivery->getDeliveryName(),
                    'CURRENCY'              => $order->getCurrency(),
                    'PRICE_DELIVERY'        => $selectedDelivery->getPrice(),
                    'CUSTOM_PRICE_DELIVERY' => 'Y',
                ]
            );
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to set shipment fields: %s', $e->getMessage()), [
                'deliveryId' => $selectedDelivery->getDeliveryId(),
            ]);
            throw new OrderCreateException('Failed to create order shipment');
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
                        /** @var DeliveryResultInterface $selectedDelivery */
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
                    $value = ($this->deliveryService->isDelivery($selectedDelivery) && !$selectedDelivery->getStockResult()->getDelayed()->isEmpty())
                        ? BitrixUtils::BX_BOOL_TRUE
                        : BitrixUtils::BX_BOOL_FALSE;
                    break;
                default:
                    continue 2;
            }

            $propertyValue->setValue($value);
        }

        /**
         * Заполнение складов довоза товара для элементов корзины (кроме доставок 04 и 06)
         */
        if (!($selectedDelivery->getStockResult()->getDelayed()->isEmpty() &&
            (
                ($this->deliveryService->isInnerDelivery($selectedDelivery) && $selectedDelivery->getSelectedStore()->isShop()) ||
                $this->deliveryService->isInnerPickup($selectedDelivery)
            ))
        ) {
            $shipmentResults = $selectedDelivery->getShipmentResults();
            $shipmentDays = [];
            /** @var BasketItem $item */
            foreach ($order->getBasket()->getOrderableItems() as $item) {
                $shipmentPlaceCode = 'DC01';
                /** @var DeliveryScheduleResult $deliveryResult */
                if ($shipmentResults &&
                    ($deliveryResult = $shipmentResults->getByOfferId($item->getProductId()))
                ) {
                    $shipmentPlaceCode = $deliveryResult->getScheduleResult()->getSenderCode() ?: $shipmentPlaceCode;
                    $days = $deliveryResult->getScheduleResult()->getDays($selectedDelivery->getCurrentDate());
                    if (!isset($shipmentDays[$shipmentPlaceCode]) || $shipmentDays[$shipmentPlaceCode] < $days) {
                        $shipmentDays[$shipmentPlaceCode] = $days;
                    }
                }

                $this->basketService->setBasketItemPropertyValue(
                    $item,
                    'SHIPMENT_PLACE_CODE',
                    $shipmentPlaceCode
                );
            }
            if (!empty($shipmentDays)) {
                arsort($shipmentDays);
                $this->setOrderPropertyByCode($order, 'SHIPMENT_PLACE_CODE', key($shipmentDays));
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
                    if (!$innerPayment = $paymentCollection->getInnerPayment()) {
                        $innerPayment = $paymentCollection->createInnerPayment();
                    }
                    $innerPayment->setField('SUM', $storage->getBonus());
                    $innerPayment->setPaid('Y');
                    $sum -= $storage->getBonus();
                }
            } catch (\Exception $e) {
                $this->log()->error(sprintf('bonus payment failed: %s', $e->getMessage()), [
                    'userId'  => $storage->getUserId(),
                    'fuserId' => $storage->getFuserId(),
                ]);
                throw new OrderCreateException('Bonus payment failed');
            }

            try {
                $extPayment = $paymentCollection->createItem();
                $extPayment->setField('SUM', $sum);
                $extPayment->setField('PAY_SYSTEM_ID', $storage->getPaymentId());
                /** @var Service $paySystem */
                $paySystem = $extPayment->getPaySystem();
                $extPayment->setField('PAY_SYSTEM_NAME', $paySystem->getField('NAME'));
            } catch (\Exception $e) {
                $this->log()->error(sprintf('order payment failed: %s', $e->getMessage()), [
                    'userId'  => $storage->getUserId(),
                    'fuserId' => $storage->getFuserId(),
                    'paymentId' => $storage->getPaymentId()
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
        foreach ($propertyValueCollection as $propertyValue) {
            $code = $propertyValue->getProperty()['CODE'];
            if ($skipAddressProperties && \in_array($code, $addressProperties, true)) {
                continue;
            }

            $key = 'PROPERTY_' . $code;

            $value = null;
            if (isset($arrayStorage[$key])) {
                $value = $arrayStorage[$key];
            } else {
                switch($code) {
                    case 'PROMOCODE':
                        $value = $this->couponStorage->getApplicableCoupon();
                        break;
                }
            }

            if (null !== $value) {
                $propertyValue->setValue($value);
            }
        }

        if ($storage->isFastOrder()) {
            $fastOrderProperties = [
                'NAME',
                'EMAIL',
                'PHONE',
                'PHONE_ALT',
                'CITY',
                'CITY_CODE',
                'COM_WAY',
                'DELIVERY_PLACE_CODE',
                'IS_FAST_ORDER',
            ];

            /** @var PropertyValue $propertyValue */
            foreach ($propertyValueCollection as $propertyValue) {
                $code = $propertyValue->getProperty()['CODE'];
                $value = $propertyValue->getValue();

                if (!\in_array($code, $fastOrderProperties, true)) {
                    $value = null;
                } else {
                    switch ($code) {
                        case 'IS_FAST_ORDER':
                            $value = 'Y';
                            break;
                        case 'CITY':
                            $value = $storage->getCity();
                            break;
                        case 'CITY_CODE':
                            $value = $storage->getCityCode();
                            break;
                        case 'DELIVERY_PLACE_CODE':
                            switch ($selectedDelivery->getDeliveryZone()) {
                                case DeliveryService::ZONE_1:
                                case DeliveryService::ZONE_3:
                                    $value = 'DC01';
                                    break;
                                case DeliveryService::ZONE_2:
                                    if ($this->deliveryService->isDelivery($selectedDelivery)) {
                                        $value = $selectedDelivery->getSelectedStore()->getXmlId();
                                    } elseif ($baseShop = $selectedDelivery->getBestShops()->getBaseShops()->first()) {
                                        $value = $baseShop->getXmlId();
                                    }
                                    break;
                            }
                    }
                }

                $propertyValue->setValue($value);
            }
        }

        if ($isDiscountEnabled) {
            Manager::enableExtendsDiscount();
        }

        return $order;
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param Order                           $order
     * @param OrderStorage                    $storage
     * @param CalculationResultInterface|null $selectedDelivery
     *
     * @throws NotFoundException
     * @throws ValidationException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws BitrixRuntimeException
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
        ?CalculationResultInterface $selectedDelivery = null
    ): void {
        $fastOrder = $storage->isFastOrder();

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
        if ($storage->getDiscountCardNumber()) {
            try {
                $canAttachCard = $this->manzanaService->validateCardByNumber($storage->getDiscountCardNumber());
            } catch (ManzanaServiceException $e) {}
        }

        if ($storage->getUserId()) {
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

            if ($canAttachCard && !$user->getDiscountCardNumber()) {
                try {
                    $this->manzanaService->addUserBonusCard($user, $storage->getDiscountCardNumber());
                } catch (ManzanaServiceException|ManzanaException $e) {
                    $this->log()->error(
                        sprintf('failed to add user bonus card: %s: %s', \get_class($e), $e->getMessage()),
                        ['user' => $user->getId(), 'card' => $storage->getDiscountCardNumber()]
                    );
                }
            }
        } else {
            $user = null;
            try {
                $user = $this->userProvider->findOneByPhoneOrEmail($storage->getPhone(), $storage->getEmail());
            } catch (UserNotFoundException $e) {
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

                if ($canAttachCard) {
                    $user->setDiscountCardNumber($storage->getDiscountCardNumber());
                }

                $_SESSION['SEND_REGISTER_EMAIL'] = true;
                $user = $this->userRegistrationProvider->register($user);

                try {
                    $client = new Client();
                    $this->currentUserProvider->setClientPersonalDataByCurUser($client, $user);
                    $this->manzanaService->updateContact($client)->contactId;
                } catch (ContactUpdateException | ManzanaServiceException $e) {
                    $this->log()->error(
                        sprintf(
                            'failed to create manzana contact: %s: %s',
                            \get_class($e),
                            $e->getMessage()
                        ),
                        ['user' => $user->getId()]
                    );
                }

                /** @noinspection PhpInternalEntityUsedInspection */
                $order->setFieldNoDemand('USER_ID', $user->getId());
                $needCreateAddress = true;
                $newUser = true;

                /* @todo вынести из сессии? */
                /* нужно для expertsender */
                $_SESSION['NEW_USER'] = [
                    'LOGIN'    => $storage->getPhone(),
                    'PASSWORD' => $password,
                ];

                $storage->setUserId($user->getId());
            }
        }

        $this->setOrderPropertyByCode(
            $order,
            'USER_REGISTERED',
            $newUser ? BitrixUtils::BX_BOOL_FALSE : BitrixUtils::BX_BOOL_TRUE
        );

        if (!$user->getDiscountCardNumber() && !$storage->getDiscountCardNumber()) {
            try {
                $contact = $this->manzanaService->getContactByPhone(PhoneHelper::getManzanaPhone($storage->getPhone()));
                if (($card = $contact->getCards()->first()) instanceof Card) {
                    $storage->setDiscountCardNumber($card->cardNumber);
                }
            } catch (WrongPhoneNumberException $e) {
            } catch (ManzanaServiceContactSearchNullException $e) {
            } catch (ManzanaServiceException $e) {
                $this->log()->error(sprintf('failed to get discount card number: %s', $e->getMessage()), [
                    'phone' => $storage->getPhone(),
                ]);
            }
        }

        $this->setOrderPropertyByCode(
            $order,
            'DISCOUNT_CARD',
            $this->manzanaService->prepareCardNumber(
                $user->getDiscountCardNumber() ?: $storage->getDiscountCardNumber()
            )
        );

        $address = null;
        if (!$fastOrder) {
            if ($this->deliveryService->isDelivery($selectedDelivery)) {
                /**
                 * Для доставки - сохраняем адрес
                 */
                $address = $this->compileOrderAddress($order);

                if ($needCreateAddress) {
                    $personalAddress = $this->addressService->createFromLocation($address)
                        ->setUserId($order->getUserId());

                    try {
                        $this->addressService->add($personalAddress);
                        $storage->setAddressId($personalAddress->getId());
                    } catch (\Exception $e) {
                        $this->log()->error(sprintf('failed to save address: %s', $e->getMessage()), [
                            'city'     => $personalAddress->getCity(),
                            'location' => $personalAddress->getCityLocation(),
                            'userId'   => $personalAddress->getUserId(),
                            'street'   => $personalAddress->getStreet(),
                            'house'    => $personalAddress->getHouse(),
                            'housing'  => $personalAddress->getHousing(),
                            'entrance' => $personalAddress->getEntrance(),
                            'floor'    => $personalAddress->getFloor(),
                            'flat'     => $personalAddress->getFlat(),
                        ]);
                    }
                }

                try {
                    $address = $this->locationService->splitAddress((string)$address, $storage->getCityCode());
                    if (!$address->getStreet()) {
                        $address->setValid(false);
                        $address->setStreet($storage->getStreet());
                    }
                    $this->setOrderPropertiesByCode($order, [
                        'STREET' => $address->getStreet(),
                        'STREET_PREFIX' => $address->getStreetPrefix(),
                        'ZIP_CODE' => $address->getZipCode()
                    ]);
                } catch (AddressSplitException $e) {
                    $this->log()->error(sprintf('failed to split delivery address: %s', $e->getMessage()), [
                        'fuserId' => $storage->getFuserId(),
                        'userId'  => $storage->getUserId(),
                        'address' => $address
                    ]);
                }
            } else {
                /**
                 * Для самовывоза разбиваем адрес магазина и сохраняем в свойствах заказа
                 */

                /** @var PickupResultInterface $selectedDelivery */
                $shop = $selectedDelivery->getSelectedShop();
                $addressString = $this->getOrderPropertyByCode($order, 'CITY')->getValue() . ', ' . $shop->getAddress();
                try {
                    if ($shop->getXmlId() === 'R034') {
                        /** @todo костыль. У этого магазина адрес не распознается дадатой */
                        $address = (new Address())
                            ->setValid(true)
                            ->setCity($storage->getCity())
                            ->setLocation($storage->getCityCode())
                            ->setHouse(1)
                            ->setStreetPrefix('пос')
                            ->setStreet('Красный бор');
                    } else {
                        $address = $this->locationService->splitAddress($addressString, $shop->getLocation());
                    }
                    $this->setOrderAddress($order, $address);
                } catch (AddressSplitException $e) {
                    $this->log()->error(sprintf('failed to split shop address: %s', $e->getMessage()), [
                        'fuserId' => $storage->getFuserId(),
                        'userId'  => $storage->getUserId(),
                        'shop'    => $shop->getXmlId(),
                    ]);
                }
            }
        }

        try {
            if ($this->userAvatarAuthorization->isAvatarAuthorized()) {
                if ($operator = $this->userProvider->findOne($this->userAvatarAuthorization->getAvatarHostUserId())) {
                    $order->setField(
                        'COMMENTS',
                        sprintf('Оператор: %s (%s)', $operator->getLogin(), $operator->getFullName())
                    );
                }
            }
        } catch (UserNotFoundException $e) {
            $this->log()->error('avatar not found', [
                'fuserId' => $storage->getFuserId(),
                'userId'  => $storage->getUserId(),
                'avatarId' => $this->userAvatarAuthorization->getAvatarHostUserId()
            ]);
        }

        $this->updateCommWayProperty($order, $selectedDelivery, $fastOrder, $address);

        try {
            $result = $order->save();
            if (!$result->isSuccess()) {
                throw new OrderCreateException(implode(', ', $result->getErrorMessages()));
            }
            if (!empty($result->getWarnings())) {
                $this->log()->warning(
                    sprintf(
                        'Order %s warnings : %s',
                        $order->getId(),
                        implode(', ', $result->getWarningMessages())
                    )
                );
            }

            $this->log()->info('Order created', [
                'id' => $order->getId(),
                'storage' => $this->orderStorageService->storageToArray($storage)
            ]);
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
            'order:item:' . $order->getId()
        ]);
    }

    /**
     * Инициализирует и сохраняет заказ.
     * Выполняет разделение заказов при необходимости
     *
     * @param OrderStorage $storage
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ValidationException
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws BitrixRuntimeException
     * @throws NotFoundException
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
     * @throws \Exception
     */
    public function createOrder(OrderStorage $storage): Order
    {
        if ($isDiscountEnabled = Manager::isExtendDiscountEnabled()) {
            Manager::disableExtendsDiscount();
        }

        /**
         * Разделение заказов
         */
        $toDelete = [];
        if ($storage->isSplit()) {
            [$splitResult1, $splitResult2] = $this->orderSplitService->splitOrder($storage);

            $order = $splitResult1->getOrder();
            $storage1 = $splitResult1->getOrderStorage();
            $order2 = $splitResult2->getOrder();
            $storage2 = $splitResult2->getOrderStorage();

            $this->saveOrder($order, $storage1, $splitResult1->getDelivery());
            /** @var BasketItem $basketItem */
            foreach ($order->getBasket() as $basketItem) {
                $toDelete[$basketItem->getProductId()] += $basketItem->getQuantity();
            }
            /**
             * Если выбрано частичное получение, то второй заказ не создается
             */
            $delivery = $this->orderStorageService->getSelectedDelivery($storage);
            if (!$this->orderSplitService->canGetPartial($delivery)) {
                /**
                 * чтобы предотвратить повторное создание адреса
                 */
                $storage2->setAddressId($storage1->getAddressId());

                $this->saveOrder($order2, $storage2, $splitResult2->getDelivery());
                foreach ($order2->getBasket() as $basketItem) {
                    $toDelete[$basketItem->getProductId()] += $basketItem->getQuantity();
                }
                $this->setOrderPropertyByCode($order2, 'RELATED_ORDER_ID', $order->getId());
                try {
                    $order2->save();
                } catch (\Exception $e) {
                    $this->log()->error('failed to set related order id', [
                        'order'        => $order2->getId(),
                        'relatedOrder' => $order->getId(),
                    ]);
                }
                $this->setOrderPropertyByCode($order, 'RELATED_ORDER_ID', $order2->getId());
                try {
                    $order->save();
                } catch (\Exception $e) {
                    $this->log()->error('failed to set related order id', [
                        'order'        => $order->getId(),
                        'relatedOrder' => $order2->getId(),
                    ]);
                }
            }
        } else {
            $order = $this->initOrder($storage);
            $this->saveOrder($order, $storage);
        }

        if ($isDiscountEnabled) {
            Manager::enableExtendsDiscount();
        }

        $this->orderStorageService->clearStorage($storage);
        $this->resetBasket($toDelete);

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
        return $this->paymentService->getOrderPayment($order);
    }

    /**
     * @param Order  $order
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
     * @param Order  $order
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
     * @throws NotFoundException
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
     * @throws NotFoundException
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
        return (new OfferQuery())->withFilterParameter('=ID', $ids)->exec();
    }

    /**
     * @param Order $order
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws NotFoundException
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
        if (!$payment = PaySystemActionTable::getList(['filter' => ['CODE' => OrderPayment::PAYMENT_CASH]])->fetch()) {
            $this->log()->error('cash payment not found');
            return;
        }

        if ($discountEnabled = Manager::isExtendDiscountEnabled()) {
            Manager::disableExtendsDiscount();
        }

        $paySystemId = $payment['ID'];
        $sapConsumer = Application::getInstance()->getContainer()->get(ConsumerRegistry::class);
        $updateOrder = function (Order $order) use ($paySystemId, $sapConsumer) {
            try {
                $payment = $this->getOrderPayment($order);
                if ($payment->isPaid() ||
                    $payment->getPaySystem()->getField('CODE') !== OrderPayment::PAYMENT_ONLINE
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

        if ($discountEnabled) {
            Manager::enableExtendsDiscount();
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
     * Бонусы, начисленные за заказ
     *
     * @param Order $order
     * @param User  $user
     *
     * @return string
     * @throws NotFoundException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws UserNotFoundException
     */
    public function getOrderBonusSum(Order $order, ?User $user = null): string
    {
        $propertyValue = $this->getOrderPropertyByCode($order, 'BONUS_COUNT');

        if (!$user) {
            $user = $this->userProvider->findOne($order->getUserId());
        }

        if (null === $propertyValue->getValue()) {
            try {
                $propertyValue->setValue(0);
                if ($user->getDiscountCardNumber()) {
                    /**
                     * У юзера есть бонусная карта, а бонусы за заказ еще не начислены.
                     */

                    $chequeRequest = $this->manzanaPosService->buildRequestFromBasket(
                        $order->getBasket(),
                        $user->getDiscountCardNumber(),
                        $this->basketService
                    );
                    if ($order->getPaymentCollection()->getInnerPayment()) {
                        $chequeRequest->setPaidByBonus($order->getPaymentCollection()->getInnerPayment()->getSum());
                    }
                    $cheque = $this->manzanaPosService->processCheque($chequeRequest);
                    $propertyValue->setValue(floor($cheque->getChargedBonus()));
                }
                $order->save();
            } catch (ExecuteException $e) {
                $this->log()->error(sprintf('failed to get charged bonus: %s', $e->getMessage()), [
                    'orderId' => $order->getId(),
                ]);
            } catch (\Exception $e) {
                $this->log()->error(sprintf('failed to set charged bonus for order: %s', $e->getMessage()), [
                    'orderId' => $order->getId(),
                    'bonus'   => $propertyValue->getValue(),
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
        return $this->paymentService->isOnlinePayment($order);
    }

    /**
     * @param Order $order
     *
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
            $propValue = $this->getOrderPropertyByCode($order, 'MANZANA_NUMBER')->getValue();
            $result = !empty($propValue);
        } catch (\Exception $exception) {
            $this->log()->critical(\sprintf(
                'Order mail send error: %s',
                $exception->getMessage()
            ));
            $result = false;
        }

        return $result;
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function isOldSiteOrder(Order $order): bool
    {
        try {
            $propValue = $this->getOrderPropertyByCode($order, 'IS_OLD_SITE_ORDER')->getValue();
            $result = $propValue === BitrixUtils::BX_BOOL_TRUE;
        } catch (\Exception $exception) {
            $result = false;
        }

        return $result;
    }

    /**
     * @return null|Service
     */
    public function getCashPaySystemService(): ?Service
    {
        $paySystemService = null;
        if (!isset($this->paySystemServiceCache['cash'])) {
            $this->paySystemServiceCache['cash'] = null;
            $data = \Bitrix\Sale\PaySystem\Manager::getByCode(OrderPayment::PAYMENT_CASH_OR_CARD);
            if ($data) {
                $this->paySystemServiceCache['cash'] = new Service(
                    $data
                );
            }
        }

        if ($this->paySystemServiceCache['cash']) {
            /** @var Service $paySystemService */
            $paySystemService = clone $this->paySystemServiceCache['cash'];
        }

        return $paySystemService;
    }

    /**
     * @param Order $order
     *
     * @return Date|null
     */
    public function getOrderDeliveryDate(Order $order): ?Date
    {
        $deliveryDate = null;
        try {
            $propValue = $this->getOrderPropertyByCode($order, 'DELIVERY_DATE');
            $value = $propValue->getValue();
            if ($value instanceof Date) {
                $deliveryDate = $value;
            }
        } catch (\Exception $exception) {
            // просто вернем null
        }

        return $deliveryDate;
    }

    /**
     * @param Order                      $order
     * @param CalculationResultInterface $delivery
     * @param bool                       $isFastOrder
     * @param Address|null               $address
     *
     * @throws DeliveryNotFoundException
     */
    protected function updateCommWayProperty(
        Order $order,
        CalculationResultInterface $delivery,
        bool $isFastOrder = false,
        ?Address $address = null
    ): void {
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
                case $this->deliveryService->isDelivery($delivery) && $address && !$address->isValid():
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
     * @throws NotFoundException
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
            'STREET_PREFIX',
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
            ->setStreetPrefix($properties['STREET_PREFIX'])
            ->setHouse($properties['HOUSE'])
            ->setHousing($properties['BUILDING'])
            ->setEntrance($properties['PORCH'])
            ->setFloor($properties['FLOOR'])
            ->setFlat($properties['APARTMENT']);

        return $address;
    }

    /**
     * @param Order   $order
     * @param Address $address
     *
     * @return Order
     */
    protected function setOrderAddress(Order $order, Address $address): Order
    {
        $properties = [
            'CITY_CODE'   => $address->getLocation(),
            'CITY'        => $address->getCity(),
            'STREET'      => $address->getStreet(),
            'STREET_PREFIX' => $address->getStreetPrefix(),
            'HOUSE'       => $address->getHouse(),
            'BUILDING'    => $address->getHousing(),
            'PORCH'       => $address->getEntrance(),
            'FLOOR'       => $address->getFloor(),
            'APARTMENT'   => $address->getFlat(),
            'ZIP_CODE'    => $address->getZipCode()
        ];

        return $this->setOrderPropertiesByCode($order, $properties);
    }

    /**
     * @param array $toDelete
     */
    protected function resetBasket(array $toDelete = [])
    {
        $basket = $this->basketService->getBasket();
        $allowedProperties = ['PRODUCT.XML_ID', 'CATALOG.XML_ID'];
        try {
            /** @var BasketItem $basketItem */
            foreach ($basket as $basketItem) {
                /** @var Basket $parentBasket */
                $parentBasket = $basketItem->getCollection();
                $parentOrder = $parentBasket->getOrder();
                if ($parentOrder && $parentOrder->getId()) {
                    continue;
                }

                $newQuantity = null;
                $currentQuantity = $basketItem->getQuantity();
                if (isset($toDelete[$basketItem->getProductId()]) &&
                    $toDelete[$basketItem->getProductId()] > 0
                ) {
                    $newQuantity = $currentQuantity > $toDelete[$basketItem->getProductId()]
                        ? $currentQuantity - $toDelete[$basketItem->getProductId()]
                        : 0;
                    $toDelete[$basketItem->getProductId()] -= $currentQuantity;
                }

                if (0 === $newQuantity) {
                    $basketItem->delete();
                    continue;
                }

                /** @var BasketPropertyItem $basketProperty */
                foreach ($basketItem->getPropertyCollection() as $basketProperty) {
                    if (!\in_array($basketProperty->getField('CODE'), $allowedProperties, true)) {
                        $basketProperty->delete();
                    }
                }

                $fields = [
                    'CUSTOM_PRICE' => 'N',
                    'DELAY' => 'N',
                ];
                if (null !== $newQuantity) {
                    $fields['QUANTITY'] = $newQuantity;
                }

                $basketItem->setFieldsNoDemand($fields);
            }
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf('failed to reset basketItem fields: %s: %s', \get_class($e), $e->getMessage()),
                [
                    'fuserId' => $basket->getFUserId(),
                    'id'      => $basketItem->getId(),
                ]
            );
        }
        $basket->save();
    }
}
