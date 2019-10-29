<?php

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketPropertyItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem\Service;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentItem;
use Bitrix\Sale\UserMessageException;
use COption;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\AppBundle\Exception\NotFoundException as AddressNotFoundException;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DostavistaDeliveryResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DpdPickupResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Entity\DeliveryScheduleResult;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\External\DostavistaService;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\Manzana\Exception\ManzanaException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaPosService;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\BxCollection;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\Helpers\WordHelper;
use FourPaws\LocationBundle\Entity\Address;
use FourPaws\LocationBundle\Exception\AddressSplitException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Enum\OrderStatus;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\DeliveryNotAvailableException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderCancelException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Exception\OrderExtendException;
use FourPaws\SaleBundle\Exception\OrderSplitException;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponStorageInterface;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\ScheduleResult;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\ScheduleResultService;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\NotFoundException as UserNotFoundException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAvatarAuthorizationInterface;
use FourPaws\UserBundle\Service\UserRegistrationProviderInterface;
use FourPaws\UserBundle\Service\UserSearchInterface;
use Psr\Log\LoggerAwareInterface;
use FourPaws\External\Dostavista\Model\Order as DostavistaOrder;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\External\Dostavista\Model;
use FourPaws\PersonalBundle\Service\OrderService as PersonalOrderService;
use FourPaws\SapBundle\Service\Orders\OrderService as SapOrderService;
use FourPaws\SapBundle\Service\Orders\StatusService;

/**
 * Class OrderService
 *
 * @package FourPaws\SaleBundle\Service
 */
class OrderService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const PROPERTY_TYPE_ENUM = 'ENUM';

    const ROYAL_CANIN_OFFERS = [
        1019946, 1019948, 1019947, 1019949, 1021686, 1021685, 1019821, 1002780, 1016776, 1019823, 1002783, 1002778,
        1002785, 1002776, 1002786, 1002779, 1002553, 1016215, 1001782, 1016773, 1016774, 1016775, 1016216, 1002782,
        1003903, 1002551, 1002550, 1021687, 1019835, 1006587, 1020331, 1006588, 1009598, 1002781, 1002777, 1003367,
        1002784, 1002562, 3005033, 1016213, 1016214, 1016212, 1002944, 1021688, 1006361, 1019834, 1019837, 1018224,
        1018225, 1001814, 1001915, 1001783, 1021691, 1001804, 1003082, 1001911, 1001904, 1013452, 1021689, 1001914,
        1001803, 1007282, 1006586, 1006626, 1019629, 1018336, 1001913, 1001802, 1001815, 1021690, 1003129, 1004064,
        1016244, 1001806, 1001812, 1003580, 1001907, 1001906, 1001908, 1001808, 1016245, 1001807, 1016248, 1003159,
        1003085, 1015821, 1001805, 1001910, 1001903, 1001902, 1001912, 1001905
    ];

    /**
     * РЦ Склад
     */
    public const STORE = 'DC01';

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
     * @var OrderSubscribeService
     */
    protected $orderSubscribeService;

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
     * @var SapOrderService $sapOrderService
     */
    protected $sapOrderService;

    /**
     * @var CouponStorageInterface
     */
    protected $couponStorage;

    /** @var array $paySystemServiceCache */
    private $paySystemServiceCache = [];

    /** @var string $dostavistManagerPhone */
    private $dostavistManagerPhone = '8 (495) 221-72-25, доб. 5005';

    /**
     * OrderService constructor.
     *
     * @param AddressService $addressService
     * @param BasketService $basketService
     * @param PaymentService $paymentService
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param UserSearchInterface $userProvider
     * @param DeliveryService $deliveryService
     * @param LocationService $locationService
     * @param StoreService $storeService
     * @param OrderStorageService $orderStorageService
     * @param OrderSubscribeService $orderSubscribeService
     * @param OrderSplitService $orderSplitService
     * @param UserAvatarAuthorizationInterface $userAvatarAuthorization
     * @param UserRegistrationProviderInterface $userRegistrationProvider
     * @param ManzanaPosService $manzanaPosService
     * @param ManzanaService $manzanaService
     * @param CouponStorageInterface $couponStorage
     * @param SapOrderService $sapOrderService
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
        orderSubscribeService $orderSubscribeService,
        OrderSplitService $orderSplitService,
        UserAvatarAuthorizationInterface $userAvatarAuthorization,
        UserRegistrationProviderInterface $userRegistrationProvider,
        ManzanaPosService $manzanaPosService,
        ManzanaService $manzanaService,
        CouponStorageInterface $couponStorage,
        SapOrderService $sapOrderService
    ) {
        $this->addressService = $addressService;
        $this->basketService = $basketService;
        $this->paymentService = $paymentService;
        $this->currentUserProvider = $currentUserProvider;
        $this->userProvider = $userProvider;
        $this->deliveryService = $deliveryService;
        $this->storeService = $storeService;
        $this->orderStorageService = $orderStorageService;
        $this->orderSubscribeService = $orderSubscribeService;
        $this->orderSplitService = $orderSplitService;
        $this->userAvatarAuthorization = $userAvatarAuthorization;
        $this->userRegistrationProvider = $userRegistrationProvider;
        $this->locationService = $locationService;
        $this->manzanaPosService = $manzanaPosService;
        $this->manzanaService = $manzanaService;
        $this->couponStorage = $couponStorage;
        $this->sapOrderService = $sapOrderService;
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
     * @param OrderStorage $storage
     * @param Basket|null $basket
     * @param CalculationResultInterface|null $selectedDelivery
     *
     * @return Order
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws DeliveryNotAvailableException
     * @throws DeliveryNotFoundException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderCreateException
     * @throws StoreNotFoundException
     * @throws UserMessageException
     * @throws ObjectException
     */
    public function initOrder(
        OrderStorage $storage,
        ?Basket $basket = null,
        ?CalculationResultInterface $selectedDelivery = null
    ): Order {
        $order = Order::create(SITE_ID, $storage->getUserId() ?: null);

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
                    'basket' => $this->basketService->getBasketProducts($basket),
                ]);

                throw new DeliveryNotAvailableException('No available deliveries');
            }
        }

        $selectedDelivery = clone $selectedDelivery;

        // при создании из подписки выбирается желаемая дата доставки
        if ($storage->isSubscribe()) {
            $selectedDelivery = $this->deliveryService->getNextDeliveries($selectedDelivery, 10)[$storage->getDeliveryDate()];
        }

        if (!$selectedDelivery->isSuccess()) {
            $this->log()->error('Selected delivery is not available', [
                'fuserId' => $storage->getFuserId(),
                'userId' => $storage->getUserId(),
                'location' => $storage->getCityCode(),
                'basket' => $this->basketService->getBasketProducts($basket),
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
            $basket = $basket->createClone();
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

                    /*
                     * $this->basketService->addOfferToBasket нельзя использовать, так как он обновит QUANTITY
                     * у элемента корзины, а нам нужно создать новый basketItem с DELAY => 'Y'
                     */

                    $delayBasketItem = $basket->createItem('catalog', $basketItem->getProductId());

                    $delayBasketItem->setFields([
                        'CUSTOM_PRICE' => BitrixUtils::BX_BOOL_TRUE,
                        'DELAY' => BitrixUtils::BX_BOOL_TRUE,
                    ]);

                    $delayItemPropertyCollection = $delayBasketItem->getPropertyCollection();

                    foreach ($basketItem->getPropertyCollection()->getPropertyValues() as $property) {
                        if (in_array($property['CODE'], ['CATALOG.XML_ID', 'PRODUCT.XML_ID'])) {
                            $delayItemProperty = $delayItemPropertyCollection->createItem();
                            $delayItemProperty->setFields([
                                'NAME' => $property['NAME'],
                                'CODE' => $property['CODE'],
                                'VALUE' => $property['VALUE'],
                            ]);
                        }
                    }
                }

                if (!empty($toUpdate)) {
                    $basketItem->setFields($toUpdate);
                }
            }
        }

        $order->setBasket($basket);
        if ($order->getBasket()->getOrderableItems()->isEmpty()) {
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

        if ($this->deliveryService->isDelivery($selectedDelivery) &&
            $selectedDelivery = $this->deliveryService->getNextDeliveries($selectedDelivery, 10)[$storage->getDeliveryDate()]
        ) {
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
            $shipment->setFields(
                [
                    'DELIVERY_ID'           => $selectedDelivery->getDeliveryId(),
                    'DELIVERY_NAME'         => $selectedDelivery->getDeliveryName(),
                    'CURRENCY'              => $order->getCurrency(),
                ]
            );

            /** @var BasketItem $item */
            foreach ($order->getBasket() as $item) {
                if ($item->isDelay() || !$item->canBuy()) {
                    continue;
                }
                $shipmentItem = $shipmentItemCollection->createItem($item);
                $shipmentItem->setQuantity($item->getQuantity());
            }

            $shipment->setFields(
                [
                    'PRICE_DELIVERY'        => $selectedDelivery->getPrice(),
                    'CUSTOM_PRICE_DELIVERY' => 'Y',
                ]
            );
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to set shipment fields: %s', $e->getMessage()), [
                'deliveryId' => $selectedDelivery->getDeliveryId(),
                'trace' => $e->getTrace(),
            ]);
            throw new OrderCreateException('Failed to create order shipment');
        }

        try {
            /* @todo костыль: удаление из системной отгрузки товаров не в наличии */
            if ($systemShipment = $shipmentCollection->getSystemShipment()) {
                $shipmentItemCollection = $systemShipment->getShipmentItemCollection();
                /** @var ShipmentItem $shipmentItem */
                foreach ($shipmentItemCollection as $shipmentItem) {
                    $basketItem = $shipmentItem->getBasketItem();
                    if ($basketItem->isDelay() || !$basketItem->canBuy()) {
                        $shipmentItem->setFieldNoDemand('QUANTITY', 0);
                        $shipmentItem->delete();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to update system shipment: %s', $e->getMessage()), [
                'deliveryId' => $selectedDelivery->getDeliveryId(),
                'trace' => $e->getTrace(),
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
                    } else if ($this->deliveryService->isDpdDelivery($selectedDelivery) || $this->deliveryService->isDpdPickup($selectedDelivery)) {
                        $value = $selectedDelivery->getSelectedStore()->getXmlId();
                    } else if ($this->deliveryService->isPickup($selectedDelivery)) {
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
                    } elseif ($this->deliveryService->isDostavistaDelivery($selectedDelivery)) {
                        $deliveryTo = (clone $deliveryDate)->modify(sprintf('+%s minutes', $selectedDelivery->getPeriodTo()));
                        $value = sprintf(
                            '%s:%s-%s:%s',
                            $deliveryDate->format('H'),
                            $deliveryDate->format('i'),
                            $deliveryTo->format('H'),
                            $deliveryDate->format('i')
                        );
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
                case 'DELIVERY_COST':
                    $value = $shipmentCollection->getPriceDelivery();
                    break;
                default:
                    continue 2;
            }

            $propertyValue->setValue($value);
        }
        /**
         * Заполнение координаты пользователя
         */
        $lat = $storage->getLat();
        $lng = $storage->getLng();
        $userCoords = [floatval($lat), floatval($lng)];
        if ($this->deliveryService->isDostavistaDelivery($selectedDelivery) || $this->deliveryService->isDelivery($selectedDelivery)) {
            $this->setOrderPropertiesByCode($order, ['USER_COORDS' => $lat . ',' . $lng]);
        }

        /**
         * Заполнение складов довоза товара для элементов корзины (кроме доставок 04 и 06)
         */
        if ($this->deliveryService->isDostavistaDelivery($selectedDelivery)) {
            /**
             * @var DostavistaDeliveryResult $selectedDelivery
             */
            $nearShop = $selectedDelivery->getNearShop($userCoords);
            if ($nearShop == null) {
                $nearShop = $selectedDelivery->getStockResult()->first();
            }
            $selectedDelivery->getStockResult();
            foreach ($order->getBasket() as $item) {
                $this->basketService->setBasketItemPropertyValue(
                    $item,
                    'SHIPMENT_PLACE_CODE',
                    $nearShop->getXmlId()
                );
            }
            $this->setOrderPropertiesByCode($order,
                [
                    'STORE_FOR_DOSTAVISTA' => $nearShop->getXmlId(),
                    'SHIPMENT_PLACE_CODE' => $nearShop->getXmlId(),
                ]
            );
        } elseif (
            !($selectedDelivery->getStockResult()->getDelayed()->isEmpty()
                &&
                (
                    ($this->deliveryService->isInnerDelivery($selectedDelivery) && $selectedDelivery->getSelectedStore()->isShop())
                    || $this->deliveryService->isInnerPickup($selectedDelivery)
                )
            )
            ||  mb_strpos($selectedDelivery->getDeliveryZone(), DeliveryService::ZONE_MOSCOW_DISTRICT_CODE_PATTERN) !== false
        ) {

            /**
             * Месье Костелье для районов Москвы (установка SHIPMENT_PLACE_CODE если есть базовый магазин для зоны)
             */
            $shipmentResults = $selectedDelivery->getShipmentResults();
            $shipmentDays = [];
            $isSetParam = [];
            $shipmentPlaceCodeDefault = '';
            /** @var BasketItem $item */
            foreach ($order->getBasket() as $itemKey => $item) {
                $offer = OfferQuery::getById($item->getProductId());
                $selectedShop = $selectedDelivery->getSelectedStore();

                if ($selectedShop instanceof Store) {
                    $shipmentPlaceCode = $selectedShop->getXmlId();
                } else {
                    $shipmentPlaceCode = self::STORE;
                }

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

                $arShipmentPlaceCode[$itemKey] = $shipmentPlaceCode;
                if ($offer->isAvailable() && $offer->isByRequest()) {
                    $isSetParam[] = $item->getProductId();
                    $this->basketService->setBasketItemPropertyValue(
                        $item,
                        'SHIPMENT_PLACE_CODE',
                        $shipmentPlaceCode
                    );
                } else {
                    if ($shipmentPlaceCode === self::STORE) {
                        $shipmentPlaceCodeDefault = $shipmentPlaceCode;
                    }
                }
            }

            foreach ($order->getBasket() as $itemKey => $item) {
                if (!in_array($item->getProductId(), $isSetParam)) {
                    $this->basketService->setBasketItemPropertyValue(
                        $item,
                        'SHIPMENT_PLACE_CODE',
                        $shipmentPlaceCodeDefault ?: $arShipmentPlaceCode[$itemKey]
                    );
                }
            }

            $this->setOrderPropertyByCode($order, 'SHIPMENT_PLACE_CODE', $shipmentPlaceCodeDefault);
            if (!empty($shipmentDays)) {
                arsort($shipmentDays);
                $this->setOrderPropertyByCode($order, 'SHIPMENT_PLACE_CODE', key($shipmentDays));
            }
        }

        $this->basketService->setDC01AmountProperty($basket);

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
                    'paymentId' => $storage->getPaymentId(),
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
        $skipAddressProperties = !$this->deliveryService->isDelivery($selectedDelivery) && !$this->deliveryService->isDostavistaDelivery($selectedDelivery);

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

        /**
         * [LP22-37] Сохранение информации об операторе в режиме "аватара"
         */
        if ($this->userAvatarAuthorization->isAvatarAuthorized()) {
            try {
                $operator = $this->userProvider->findOne($this->userAvatarAuthorization->getAvatarHostUserId());
                if ($operator) {
                    $this->log()->notice('Operator avatar save info', [
                        'ORDER_ID' => $order->getId(),
                        'ORDER_CODE' => $order->getField('ACCOUNT_NUMBER'),
                        'ID' => $operator->getId(),
                        'EMAIL' => $operator->getEmail(),
                        'SHOP_CODE' => $operator->getShopCode(),
                        'NAME' => $operator->getName(),
                        'SECOND_NAME' => $operator->getSecondName()
                    ]);
                    $this->setOrderPropertyByCode(
                        $order,
                        'OPERATOR_EMAIL',
                        $operator->getEmail()
                    );
                    $this->setOrderPropertyByCode(
                        $order,
                        'OPERATOR_SHOP',
                        $operator->getShopCode()
                    );
                }
            } catch (UserNotFoundException $e) {
                $this->log()->error(
                    'avatar not found',
                    [
                        'fuserId' => $storage->getFuserId(),
                        'userId'  => $storage->getUserId(),
                        'avatarId' => $this->userAvatarAuthorization->getAvatarHostUserId(),
                    ]
                );
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
                'OPERATOR_EMAIL',
                'OPERATOR_SHOP',
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
                                case DeliveryService::ZONE_5:
                                case DeliveryService::ZONE_6:
                                case DeliveryService::ZONE_IVANOVO:
                                    $value = self::STORE;
                                    break;
                                case DeliveryService::ZONE_2:
                                case DeliveryService::ZONE_NIZHNY_NOVGOROD:
                                case DeliveryService::ZONE_NIZHNY_NOVGOROD_REGION:
                                case DeliveryService::ZONE_VLADIMIR:
                                case DeliveryService::ZONE_VLADIMIR_REGION:
                                case DeliveryService::ZONE_VORONEZH:
                                case DeliveryService::ZONE_VORONEZH_REGION:
                                case DeliveryService::ZONE_YAROSLAVL:
                                case DeliveryService::ZONE_YAROSLAVL_REGION:
                                case DeliveryService::ZONE_TULA:
                                case DeliveryService::ZONE_TULA_REGION:
                                case DeliveryService::ZONE_KALUGA:
                                case DeliveryService::ZONE_KALUGA_REGION:
                                case DeliveryService::ZONE_IVANOVO_REGION:
                                    if ($this->deliveryService->isDelivery($selectedDelivery)) {
                                        $value = $selectedDelivery->getSelectedStore()->getXmlId();
                                    } elseif ($baseShop = $selectedDelivery->getBestShops()->getBaseShops()->first()) {
                                        $value = $baseShop->getXmlId();
                                    }
                                    else{
                                        $value = self::STORE;
                                    }
                                    break;
                                case DeliveryService::ZONE_MOSCOW:
                                    if ($this->deliveryService->isDostavistaDelivery($selectedDelivery)) {
                                        $value = $selectedDelivery->getSelectedStore()->getXmlId();
                                    } elseif ($baseShop = $selectedDelivery->getBestShops()->getBaseShops()->first()) {
                                        $value = $baseShop->getXmlId();
                                    }
                                    break;
                                default:
                                    if (
                                        mb_strpos($selectedDelivery->getDeliveryZone(), DeliveryService::ADD_DELIVERY_ZONE_CODE_PATTERN) !== false ||
                                        mb_strpos($selectedDelivery->getDeliveryZone(), DeliveryService::ZONE_MOSCOW_DISTRICT_CODE_PATTERN) !== false
                                    ) {
                                        if ($this->deliveryService->isDelivery($selectedDelivery)) {
                                            $baseShops = $selectedDelivery->getBestShops()->getBaseShops();
                                            if($baseShops->count() > 0){
                                                $value = $baseShops->first()->getXmlId();
                                            } else {
                                                $value = $selectedDelivery->getSelectedStore()->getXmlId();
                                            }
                                        } elseif ($baseShop = $selectedDelivery->getBestShops()->getBaseShops()->first()) {
                                            $value = $baseShop->getXmlId();
                                        } else {
                                            $value = self::STORE;
                                        }
                                    }
                            }
                    }
                }

                $propertyValue->setValue($value);
            }
        }

        /**
         * LP23-37 - Запретить ввод символа решетки в имя покупателя
         */
        $propName = $propertyValueCollection->getPayerName();
        $propName->setValue(str_replace('#', '', $propName->getValue()));

        if ($storage->isFromApp()) {
            /** @var PropertyValue $propertyValue */
            foreach ($propertyValueCollection as $propertyValue) {
                $code = $propertyValue->getProperty()['CODE'];
                if ($code === 'FROM_APP') {
                    $value = 'Y';
                    $propertyValue->setValue($value);
                    break;
                }
            }
        }


        if ($isDiscountEnabled) {
            Manager::enableExtendsDiscount();
        }

        return $order;
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param Order $order
     * @param OrderStorage $storage
     * @param CalculationResultInterface|null $selectedDelivery
     *
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
     * @throws \GuzzleHttp\Exception\GuzzleException
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
        if($storage->getDeliveryDate() > 0){
            $selectedDelivery = $this->deliveryService->getNextDeliveries($selectedDelivery, ($storage->getDeliveryDate()+1))[$storage->getDeliveryDate()];
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
                $_SESSION['NOT_MANZANA_UPDATE'] = true;
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
                        ['user' => $user->getId(), 'phone' => $storage->getPhone()]
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
//                $contact = $this->manzanaService->getContactByPhone(PhoneHelper::getManzanaPhone($storage->getPhone()));
//                if (($card = $contact->getCards()->first()) instanceof Card) {
//                    $storage->setDiscountCardNumber($card->cardNumber);
//                }

                $contactId = $this->manzanaService->getContactIdByPhone(PhoneHelper::getManzanaPhone($storage->getPhone()));
                $cards = $this->manzanaService->getCardsByContactId($contactId);
                foreach ($cards as $cardItem) {
                    if ($cardItem->isActive()) {
                        $storage->setDiscountCardNumber($cardItem->cardNumber);
                        break;
                    }
                }
            } catch (WrongPhoneNumberException $e) {
            } catch (ManzanaServiceContactSearchNullException $e) {
            } catch (ManzanaServiceException $e) {
            } catch (Exception $e) {
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

        if($storage->isSubscribe()){
            $this->setOrderPropertyByCode(
                $order,
                'IS_SUBSCRIBE',
                'Y'
            );
            $this->setOrderPropertyByCode(
                $order,
                'SUBSCRIBE_ID',
                $storage->getSubscribeId()
            );
        }

        $shipmentPlaceCode = $this->getOrderPropertyByCode($order, 'SHIPMENT_PLACE_CODE')->getValue();
        if ($shipmentPlaceCode) {
            $sender = $this->storeService->getStoreByXmlId($shipmentPlaceCode);
            //$receiver = $this->storeService->getStoreByXmlId('R111');
            $receiver = $selectedDelivery->getSelectedStore();
            $currentDate = $storage->getCurrentDate();
            $scheduleResultOptimal = $this->getScheduleResultOptimal($sender, $receiver, $currentDate, $selectedDelivery->getDeliveryDate());
            if (!empty($scheduleResultOptimal)) {
                $this->setOrderPropertyByCode($order, 'SCHEDULE_REGULARITY', $scheduleResultOptimal->getRegularityName());
            }
        }

        $address = null;
        if (!$fastOrder) {
            if ($this->deliveryService->isDelivery($selectedDelivery) || $this->deliveryService->isDostavistaDelivery($selectedDelivery)) {
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
                            'city' => $personalAddress->getCity(),
                            'location' => $personalAddress->getLocation(),
                            'userId' => $personalAddress->getUserId(),
                            'street' => $personalAddress->getStreet(),
                            'house' => $personalAddress->getHouse(),
                            'housing' => $personalAddress->getHousing(),
                            'entrance' => $personalAddress->getEntrance(),
                            'floor' => $personalAddress->getFloor(),
                            'flat' => $personalAddress->getFlat(),
                        ]);
                    }
                }

                try {
                    if (!$address->getRegion()) {
                        $location = $this->locationService->findLocationByCode($storage->getCityCode());
                        $area = [];
                        foreach ($location['PATH'] as $locationPathItem) {
                            $locationCode = $locationPathItem['CODE'];
                            $locationType = $locationPathItem['TYPE']['CODE'];
                            if (($locationType === LocationService::TYPE_REGION) ||
                                ($locationType === LocationService::TYPE_CITY && $locationCode === LocationService::LOCATION_CODE_MOSCOW)
                            ) {
                                $address->setRegion($locationPathItem['NAME']);
                            } elseif (
                            \in_array($locationType, [
                                LocationService::TYPE_SUBREGION,
                                LocationService::TYPE_CITY,
                            ], true)
                            ) {
                                $area[] = $locationPathItem['NAME'];
                            }
                        }
                        $address->setArea(\implode(', ', $area));
                    }

                    $address = $this->locationService->splitAddress((string)$address, $storage->getCityCode());
                    if (!$address->getStreet()) {
                        $address->setValid(false);
                        $address->setStreet($storage->getStreet());
                    }
                    $this->setOrderPropertiesByCode($order, [
                        'AREA' => $address->getArea(),
                        'REGION' => $address->getRegion(),
                        'STREET' => $address->getStreet(),
                        'STREET_PREFIX' => $address->getStreetPrefix(),
                        'ZIP_CODE' => $address->getZipCode(),
                    ]);
                } catch (AddressSplitException $e) {
                    $this->log()->error(sprintf('failed to split delivery address: %s', $e->getMessage()), [
                        'fuserId' => $storage->getFuserId(),
                        'userId'  => $storage->getUserId(),
                        'address' => $address,
                    ]);
                }
                //заполняем свойство "Координаты пользователя"
                $lat = $storage->getLat();
                $lng = $storage->getLng();
                $userCoords = [floatval($lat), floatval($lng)];
                if ($this->deliveryService->isDostavistaDelivery($selectedDelivery) || $this->deliveryService->isDelivery($selectedDelivery)) {
                    $this->setOrderPropertiesByCode($order, ['USER_COORDS' => $lat . ',' . $lng]);
                }

                //получаем ближайший магазин по координатам адреса пользователя и коодинатам магазинов, где все в наличие
                if ($this->deliveryService->isDostavistaDelivery($selectedDelivery)) {
                    /**
                     * @var DostavistaDeliveryResult $selectedDelivery
                     */
                    //ищем ближайший магазин для достависты
                    $nearShop = $selectedDelivery->getNearShop($userCoords);
                    $this->setOrderPropertiesByCode($order,
                        [
                            'STORE_FOR_DOSTAVISTA' => $nearShop->getXmlId(),
                            'DELIVERY_PLACE_CODE' => $nearShop->getXmlId()
                        ]
                    );
                    $order->setField('COMMENTS', 'Упаковать заказ'); //Если достависта оставляем комментарий менеджеру
                }
            } else {
                /**
                 * Для самовывоза разбиваем адрес магазина и сохраняем в свойствах заказа
                 */

                /** @var PickupResultInterface $selectedDelivery */
                $shop = $selectedDelivery->getSelectedShop();
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
                        $addressString = $this->storeService->getStoreAddress($shop) . ', ' . $shop->getAddress();
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

        if ($this->deliveryService->isDobrolapDelivery($selectedDelivery)) {
            $this->setOrderPropertiesByCode($order, ['DOBROLAP_SHELTER' => $storage->getShelter()]);
        }

        $this->updateCommWayProperty($order, $selectedDelivery, $fastOrder, $address);

        try {
            /* @todo костыль - недоступные товары не попадают в корзину заказа, но учитываются в стоимости заказа */
            $order->setFieldNoDemand(
                'PRICE',
                $order->getBasket()->getOrderableItems()->getPrice() + $order->getDeliveryPrice()
            );
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

            if ($this->deliveryService->isDostavistaDelivery($selectedDelivery)) {
                /** @var Payment $payment */
                foreach ($order->getPaymentCollection() as $payment) {
                    if (
                        $payment->getPaySystem()->getField('CODE') === OrderPayment::PAYMENT_CASH_OR_CARD ||
                        $payment->getPaySystem()->getField('CODE') === OrderPayment::PAYMENT_CASH ||
                        $payment->getPaySystem()->getField('CODE') === OrderPayment::PAYMENT_ONLINE && $payment->isPaid()
                    ) {
                        if ($nearShop == null) {
                            $nearShop = $selectedDelivery->getStockResult()->first();
                        }

                        $isExportedToQueue = BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), 'IS_EXPORTED_TO_DOSTAVISTA_QUEUE')->getValue();
                        if ($isExportedToQueue != BitrixUtils::BX_BOOL_TRUE) {
                            $this->sendToDostavistaQueue($order, $storage->getName(), $storage->getPhone(), $storage->getComment(), $selectedDelivery->getPeriodTo(), $nearShop, false);
                        }
                        break;
                    }
                }
            }

            // активация подписки на доставку
            if($storage->isSubscribe() && $storage->getSubscribeId() > 0){
                $this->orderSubscribeService->activateSubscription($storage, $order);
            }

        } catch (\Exception $e) {
            /** ошибка при создании заказа - удаляем ошибочный заказ, если он был создан */
            if ($order->getId() > 0) {
                Order::delete($order->getId());
            }
            $this->log()->error(sprintf('failed to create order: %s', $e->getMessage()), [
                'fuserId' => $storage->getFuserId(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new OrderCreateException('failed to save order');
        }

        TaggedCacheHelper::clearManagedCache([
            'order:' . $order->getField('USER_ID'),
            'order:item:' . $order->getId(),
        ]);
    }

    /**
     * Инициализирует и сохраняет заказ.
     * Выполняет разделение заказов при необходимости
     *
     * @param OrderStorage $storage
     * @return Order
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws BitrixProxyException
     * @throws DeliveryNotAvailableException
     * @throws DeliveryNotFoundException
     * @throws LoaderException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderCreateException
     * @throws OrderSplitException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws UserMessageException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createOrder(OrderStorage $storage): Order
    {
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
            $this->resetBasket($toDelete);
        } else {
            $selectedDelivery = $this->orderStorageService->getSelectedDelivery($storage);
            $order = $this->initOrder($storage, null, $selectedDelivery);
            $this->saveOrder($order, $storage, $selectedDelivery);
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
            case ($deliveryCode == DeliveryService::DELIVERY_DOSTAVISTA_CODE):
                $address = $this->compileOrderAddress($order)->toStringExt();
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
     *
     * @return string
     * @throws NotFoundException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     */
    public function getOrderBonusSum(Order $order): string
    {
        $propertyValue = $this->getOrderPropertyByCode($order, 'BONUS_COUNT');

        if (null === $propertyValue->getValue()) {
            try {
                $propertyValue->setValue(0);
                $discountCard = $this->getOrderPropertyByCode($order, 'DISCOUNT_CARD')->getValue();
                if ($discountCard) {
                    $chequeRequest = $this->manzanaPosService->buildRequestFromBasket(
                        $order->getBasket(),
                        $discountCard,
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
     * @param Order $order
     * @param CalculationResultInterface $delivery
     * @param bool $isFastOrder
     * @param Address|null $address
     * @param bool $dostavistaSuccess
     * @throws DeliveryNotFoundException
     */
    public function updateCommWayProperty(
        Order $order,
        CalculationResultInterface $delivery,
        bool $isFastOrder = false,
        ?Address $address = null,
        bool $dostavistaSuccess = null
    ): void {
        $commWay = $this->getOrderPropertyByCode($order, 'COM_WAY');
        $value = $commWay->getValue();
        $changed = false;

        $propCopyOrderId = $this->getOrderPropertyByCode($order, 'COPY_ORDER_ID');
        if (($value === OrderPropertyService::COMMUNICATION_ADDRESS_ANALYSIS) && ($propCopyOrderId) && boolval($propCopyOrderId->getValue())) {
            /*
             * при создании заказов по подписке dadata может неправильно определить местоположение и выставляет это поле ранее
             */
            return;
        }

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
                case $this->deliveryService->isDelivery($delivery) && $address && !$address->isValid():
                    $value = OrderPropertyService::COMMUNICATION_ADDRESS_ANALYSIS;
                    break;
                case $this->isSubscribe($order):


                    $isFirsSubscribeOrder = ($propCopyOrderId) ? !\boolval($propCopyOrderId->getValue()) : true;

                    switch (true) {
                        case ($isFirsSubscribeOrder && (($value == OrderPropertyService::COMMUNICATION_SMS) || $delivery->getSelectedStore()->isShop())):
                            $value = OrderPropertyService::COMMUNICATION_FIRST_SUBSCRIBE_SMS;
                            break;
                        case ($isFirsSubscribeOrder && ($value == OrderPropertyService::COMMUNICATION_PHONE) && !$delivery->getSelectedStore()->isShop()):
                            $value = OrderPropertyService::COMMUNICATION_FIRST_SUBSCRIBE_PHONE;
                            break;
                        default:
                            $value = OrderPropertyService::COMMUNICATION_SUBSCRIBE;
                            break;
                    }
                    break;
                // способ получения 07
                case $this->deliveryService->isDpdPickup($delivery):
                case $this->deliveryService->isDpdDelivery($delivery):
                    $value = OrderPropertyService::COMMUNICATION_PHONE;
                    break;
                // способ получения 04
                case $this->deliveryService->isInnerPickup($delivery) && $stockResult->getDelayed()->isEmpty():
                    // способ получения 06
                case $this->deliveryService->isDostavistaDelivery($delivery):
                case $deliveryFromShop && $stockResult->getDelayed()->isEmpty():
                    $value = OrderPropertyService::COMMUNICATION_SMS;
                    break;
            }
        }

        $commWay->setValue($value);
    }

    /**
     * @param Order $order
     * @param $deliveryCode
     * @param Address|null $address
     * @param bool $dostavistaSuccess
     */
    public function updateCommWayPropertyEx(
        Order $order,
        $deliveryCode,
        ?Address $address = null,
        bool $dostavistaSuccess = null
    ): void {
        $commWay = $this->getOrderPropertyByCode($order, 'COM_WAY');
        $value = $commWay->getValue();

        if ($deliveryCode == DeliveryService::DELIVERY_DOSTAVISTA_CODE) {
            if ($dostavistaSuccess) {
                if ($value != OrderPropertyService::COMMUNICATION_PAYMENT_ANALYSIS) {
                    $value = OrderPropertyService::COMMUNICATION_SMS;
                }
            } else {
                if ($value == OrderPropertyService::COMMUNICATION_PAYMENT_ANALYSIS) {
                    $value = OrderPropertyService::COMMUNICATION_PAYMENT_ANALYSIS_DOSTAVISTA_ERROR;
                } else {
                    $value = OrderPropertyService::COMMUNICATION_DOSTAVISTA_ERROR;
                }
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
    public function compileOrderAddress(Order $order): Address
    {
        $properties = $this->getOrderPropertiesByCode($order, [
            'REGION',
            'AREA',
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
            ->setRegion($properties['REGION'])
            ->setArea($properties['AREA'])
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
    public function setOrderAddress(Order $order, Address $address): Order
    {
        $properties = [
            'REGION'        => $address->getRegion(),
            'AREA'          => $address->getArea(),
            'CITY_CODE'     => $address->getLocation(),
            'CITY'          => $address->getCity(),
            'STREET'        => $address->getStreet(),
            'STREET_PREFIX' => $address->getStreetPrefix(),
            'HOUSE'         => $address->getHouse(),
            'BUILDING'      => $address->getHousing(),
            'PORCH'         => $address->getEntrance(),
            'FLOOR'         => $address->getFloor(),
            'APARTMENT'     => $address->getFlat(),
            'ZIP_CODE'      => $address->getZipCode(),
        ];

        return $this->setOrderPropertiesByCode($order, $properties);
    }

    /**
     * @param array $toDelete
     */
    protected function resetBasket(array $toDelete = [])
    {
        $basket = $this->basketService->getBasket(true);
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

    public function getOrderFeedbackLink(Order $order): string
    {
        //return sprintf('/sale/order/interview/%d/?HASH=%s', $order->getId(), $order->getHash());

        $serverName = $_SERVER['SERVER_NAME'];
        if (strlen($serverName) <= 0)
            $serverName = COption::GetOptionString('main', 'server_name', '');

        $serverName = str_replace(array("https://", "http://"), '', $serverName);
        $protocol = \CMain::IsHTTPS() ? "https://" : "http://";
        return $protocol.$serverName.sprintf('/sale/order/interview/%d/?HASH=%s', $order->getId(), $order->getHash());
    }


    /**
     * Собирает массив для передачи в очередь RabbitMQ
     *
     * @param Order $order
     * @param string $name
     * @param string $phone
     * @param string $comment
     * @param string $periodTo
     * @param Store|null $nearShop
     * @param bool $isPaid
     * @return void
     * @throws AddressSplitException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendToDostavistaQueue(Order $order, string $name, string $phone, string $comment, string &$periodTo, Store $nearShop = null, bool $isPaid = false): void
    {
        $curDate = new \DateTime;
        $basket = $order->getBasket();
        /** @var int $insurance Цена страхования */
        $deliveryPrice = $order->getDeliveryPrice();
        $insurance = ceil((float)$basket->getPrice());
        $takingAmount = 0;
        if (!$isPaid) {
            $takingAmount = ceil($insurance + $deliveryPrice - $order->getSumPaid());
        }
        /** @var OfferCollection $offers */
        $offers = $this->getOrderProducts($order);
        /** @var int $weight Вес всех товаров */
        $weight = (int)($basket->getWeight() / 1000);

        switch (true) {
            case $weight <= 15:
                $vehicleTypeId = 6;
                break;
            case $weight <= 200:
                $vehicleTypeId = 7;
                break;
            case $weight <= 500:
                $vehicleTypeId = 1;
                break;
            case $weight <= 700:
                $vehicleTypeId = 2;
                break;
            case $weight <= 1000:
                $vehicleTypeId = 3;
                break;
            case $weight <= 1500:
                $vehicleTypeId = 4;
                break;
            default:
                $vehicleTypeId = 5;
                break;
        }

        $whatDeliverySet = \COption::GetOptionString('articul.dostavista.delivery', 'what_deliver_set') == BaseEntity::BITRIX_TRUE;
        $whatDeliveryText = \COption::GetOptionString('articul.dostavista.delivery', 'what_deliver_text');
        if($whatDeliverySet  && $whatDeliveryText){
            $matter = $whatDeliveryText;
        } else {
            $arSectionsNames = [];
            /** @var Offer $offer */
            foreach ($offers as $offer) {
                $section = $offer->getProduct()->getSection();
                if ($section != null) {
                    $arSectionsNames[$section->getId()] = $section->getName();
                }
            }

            /** @var string $matter Что везем - названия всех разделов через запятую */
            $matter = implode(', ', $arSectionsNames);
            unset($arSectionsNames);
        }

        /** @var int $loadersCount требуемое число грузчиков */
        $loadersCount = 0;
        /** @var Offer $offer */
        foreach ($offers as $offer) {
            $length = WordHelper::showLengthNumber($offer->getCatalogProduct()->getLength());
            $width = WordHelper::showLengthNumber($offer->getCatalogProduct()->getWidth());
            $height = WordHelper::showLengthNumber($offer->getCatalogProduct()->getHeight());
            if ($length > 170 || $width > 170 || $height > 170) {
                //портер с грузчиком
                $vehicleTypeId = 3;
                $loadersCount = 2;
                //время доставки 5 часов
                $periodTo = 300;
            } elseif ($length > 150 || $weight > 150 || $height > 150) {
                //Каблук
                $vehicleTypeId = 2;
                //время доставки 5 часов
                $periodTo = 300;
                //с грузчиком если вес больше 25кг
                if ($weight >= 25) {
                    //Каблук с грузчика
                    $loadersCount = 2;
                }
            }
        }

        $data = [
            'bitrix_order_id' => $order->getId(),
            'order_create_date' => $curDate->format('d.m.Y H:i:s'),
            'total_weight_kg' => $weight,
            'vehicle_type_id' => $vehicleTypeId,
            'matter' => $matter, //что везем
            'insurance_amount' => ceil($insurance + $deliveryPrice), //сумма страхования = цене корзины
            'is_client_notification_enabled' => (\COption::GetOptionString('articul.dostavista.delivery', 'sms_courier_set', '') == BaseEntity::BITRIX_TRUE) ? 1 : 0, //Отправить sms о назначении курьера на заказ 0/1
            'is_contact_person_notification_enabled' => (\COption::GetOptionString('articul.dostavista.delivery', 'sms_courier_time_phone', '') == BaseEntity::BITRIX_TRUE) ? 1 : 0, //Отправить получателям sms с интервалом прибытия и телефоном курьера: 0 - не отправлять, 1 - отправлять.
            'loaders_count' => $loadersCount
        ];

        $nearAddress = $this->locationService->splitAddress($nearShop->getAddress(), $nearShop->getLocation())->toStringExt();
        $secondAddress = $this->getOrderDeliveryAddress($order);

        $pointZeroDate = clone $curDate;
        $requireTimeStart = $pointZeroDate->format('c');

        $pointZeroDate->modify(\sprintf('+%s minutes', $periodTo));
        $hours = $pointZeroDate->format('H');
        $minutes = $pointZeroDate->format('i');

        if (0 <= $minutes && $minutes <= 30) {
            $minutes = 0;
        } elseif (30 < $minutes && $minutes <= 59) {
            $minutes = 30;
        }

        $pointZeroDate->setTime($hours, $minutes, 0);

        $storePhone = str_replace(['+', '(', ')', ' ', '-'], ['', '', '', '', ''], $nearShop->getPhone());
        $storePhone = explode(',доб.', $storePhone)[0];

        $data['points'][0] = [
            'address' => $nearAddress,
            'contact_person' => [
                'phone' => $storePhone
            ],
            'client_order_id' => $order->getField('ACCOUNT_NUMBER'),
            'required_start_datetime' => $requireTimeStart,
            'required_finish_datetime' => $pointZeroDate->format('c'),
            'taking_amount' => 0,
            'buyout_amount' => $takingAmount,
            'note' => 'Телефон магазина: ' . $this->dostavistManagerPhone
        ];

        $data['points'][1] = [
            'address' => $secondAddress,
            'contact_person' => [
                'phone' => $phone,
                'name' => $name
            ],
            'client_order_id' => $order->getField('ACCOUNT_NUMBER'),
            'required_start_datetime' => $requireTimeStart,
            'required_finish_datetime' => $pointZeroDate->format('c'),
            'taking_amount' => $takingAmount,
            'buyout_amount' => 0,
            'note' => $comment
        ];

        $res = $this->addDostavistaOrderToQueue($data);
        if ($res) {
            $this->log()->notice('Success send new order dostavista message to RabbitMQ', $data);
            $this->setOrderPropertiesByCode(
                $order,
                [
                    'IS_EXPORTED_TO_DOSTAVISTA_QUEUE' => BitrixUtils::BX_BOOL_TRUE
                ]
            );
            $order->save();
        }
    }

    /**
     * Структура данных + запись в очередь
     *
     * @param array $data
     * @return bool
     */
    public function addDostavistaOrderToQueue(array $data): bool
    {
        /** @var DostavistaService $dostavistaService */
        $dostavistaService = Application::getInstance()->getContainer()->get('dostavista.service');
        $dostavistaOrder = new DostavistaOrder();
        $dostavistaOrder->bitrixOrderId = $data['bitrix_order_id'];
        $dostavistaOrder->totalWeightKg = $data['total_weight_kg'];
        $dostavistaOrder->vehicleTypeId = $data['vehicle_type_id'];
        $dostavistaOrder->loadersCount = $data['loaders_count'];
        $dostavistaOrder->matter = $data['matter'];
        $dostavistaOrder->insuranceAmount = $data['insurance_amount'];
        $dostavistaOrder->isClientNotificationEnabled = $data['is_client_notification_enabled'];
        $dostavistaOrder->isContactPersonNotificationEnabled = $data['is_contact_person_notification_enabled'];
        $dostavistaOrder->orderCreateDate = $data['order_create_date'];

        $pointCollection = new ArrayCollection();
        foreach ($data['points'] as $point) {
            $contactPerson = new Model\ContactPerson();
            $contactPerson->phone = $point['contact_person']['phone'];
            if (!empty($point['contact_person']['name'])) {
                $contactPerson->name = $point['contact_person']['name'];
            } else {
                $contactPerson->name = '';
            }

            $modelPoint = new Model\Point();
            $modelPoint->address = $point['address'];
            $modelPoint->contactPerson = $contactPerson;
            $modelPoint->clientOrderId = $point['client_order_id'];
            $modelPoint->requiredStartDatetime = $point['required_start_datetime'];
            $modelPoint->requiredFinishDatetime = $point['required_finish_datetime'];
            $modelPoint->takingAmount = $point['taking_amount'];
            $modelPoint->buyoutAmount = $point['buyout_amount'];
            $modelPoint->note = $point['note'];

            $pointCollection->add($modelPoint);
        }

        $dostavistaOrder->points = $pointCollection;

        return $dostavistaService->dostavistaOrderAdd($dostavistaOrder);
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function checkRoyalCaninAction(Order $order): bool
    {
        $res = false;
        $orderPrice = $order->getPrice();
        $basketItemsXmlId = [];
        foreach ($order->getBasket() as $basketItem) {
            $basketItemsXmlId[] = $this->basketService->getBasketItemXmlId($basketItem);
        }
        $basketRoyalCaninItems = array_uintersect(static::ROYAL_CANIN_OFFERS, $basketItemsXmlId, 'strcasecmp');
        $curTime = new \DateTime();
        $dateTimeStart = \DateTime::createFromFormat('d.m.Y H:i:s', '08.04.2019 00:00:00');
        $dateTimeFinish = \DateTime::createFromFormat('d.m.Y H:i:s', '03.06.2019 23:59:59');
        if ($curTime >= $dateTimeStart && $curTime <= $dateTimeFinish && $orderPrice > 1000 && count($basketRoyalCaninItems) > 0) {
            $res = true;
        }
        return $res;
    }

    /**
     * @param Store $sender
     * @param Store $receiver
     * @param \DateTime $currentDate
     * @param \DateTime $deliveryDate
     * @return mixed|null
     */
    protected function getScheduleResultOptimal(Store $sender, Store $receiver, \DateTime $currentDateOrig, \DateTime $deliveryDate)
    {
        $scheduleResultOptimal = null;
        $currentDate = clone $currentDateOrig;
        try {
            /** @var ScheduleResultService $scheduleResultService */
            $scheduleResultService = Application::getInstance()->getContainer()->get(ScheduleResultService::class);

            /** @var ScheduleResult $scheduleResult */
            foreach ($scheduleResultService->findResultsBySenderAndReceiver($sender, $receiver)->filterByDateActiveEqual(clone $currentDate) as $scheduleResult) {

                // нужно добавить срок поставки магазина,
                // т.к. товар готов к выдаче не сразу (кроме нерегулярок)
                $modifier = 0;
                if ($receiver->isShop() && !$scheduleResult->isIrregular()) {
                    $modifier = $receiver->getDeliveryTime();
                    if ($modifier < 1) {
                        $modifier = 1;
                    }
                }

                $daysSchedule = $scheduleResult->getDays($currentDate);
                if ($daysSchedule === ScheduleResult::RESULT_ERROR) {
                    continue;
                }
                $daysSchedule += $modifier;

                $daysDelivery = $deliveryDate->setTime(0,0,0)->diff($currentDate->setTime(0,0,0))->days;

                // не успеваем доставить в срок по этому расписанию
                if($daysDelivery - $daysSchedule < 0){
                    continue;
                }

                if(!$scheduleResultOptimal){
                    $scheduleResultOptimal = $scheduleResult;
                    continue;
                }

                if($scheduleResult->getRegularitySort() < $scheduleResultOptimal->getRegularitySort()) {
                    $scheduleResultOptimal = $scheduleResult;
                }

                // если срок доставки в магазин меньше даты доставки клиенту,
                // то выбираем расписание по сортировке
//                if ($daysDelivery - $daysSchedule <= 0) {
//                    $regularitySort = $scheduleResult->getRegularitySort();
//                    if($regularitySort < $scheduleResultOptimal->getRegularitySort()) {
//                        $scheduleResultOptimal = $scheduleResult;
//                    }
//                } else if($daysSchedule < $daysScheduleOptimal) {
//                    $scheduleResultOptimal = $scheduleResult;
//                }
            }
        } catch (\Exception $e) {
            // просто не проставится регулярность
        }

        return $scheduleResultOptimal;
    }

    /**
     * @param $orderId
     * @return mixed
     * @throws OrderCancelException
     * @throws SqlQueryException
     */
    public function cancelOrder($orderId): bool
    {
        // ищем заказ
        try {
            $order = $this->getOrderById($orderId);
        } catch (\Exception $e) {
            throw new NotFoundException('Заказ не найден');
        }

        // валидация пользователя
        $userId = $order->getField('USER_ID');

        if (!$userId) {
            throw new OrderCancelException('Вы не можете отменить этот заказ');
        }

        try {
            if ((int)$userId !== $this->userProvider->getCurrentUserId()) {
                throw new OrderCancelException('Вы не можете отменить этот заказ');
            }
        } catch (NotAuthorizedException $e) {
            throw new OrderCancelException('Вы не можете отменить этот заказ');
        }

        // валидация статуса
        $statusId = $order->getField('STATUS_ID');

        if (!$statusId || in_array($statusId, PersonalOrderService::STATUS_FINAL, true) || (in_array($statusId, PersonalOrderService::STATUS_CANCEL, true))) {
            return false;
        }

        $sendEmail = false;
        if (($statusId === OrderStatus::STATUS_IN_PROGRESS) || ($statusId === OrderStatus::STATUS_DELIVERING)) {
            $sendEmail = true;
        }

        // формируем новый статус в зависимости от службы доставки
        $deliveryId = $order->getField('DELIVERY_ID');

        if (!$deliveryId) {
            throw new OrderCancelException('Не найдена служба доставки для заказа');
        }

        try {
            $deliveryCode = $this->deliveryService->getDeliveryCodeById($deliveryId);
            if ($this->deliveryService->isDeliveryCode($deliveryCode)) {
                $newStatus = OrderStatus::STATUS_CANCEL_COURIER;
            } else if ($this->deliveryService->isPickupCode($deliveryCode)) {
                $newStatus = OrderStatus::STATUS_CANCEL_PICKUP;
            } else {
                throw new OrderCancelException('Не найдена служба доставки для заказа');
            }
        } catch (\Exception $e) {
            throw new OrderCancelException('Не найдена служба доставки для заказа');
        }

        $connection = BitrixApplication::getConnection();

        $connection->startTransaction();

        try {
            // отменяем заказ в Sap'е
            $orderNumber = $order->getField('ACCOUNT_NUMBER');
            $sapStatus = StatusService::STATUS_CANCELED;
            $setStatusResult = $this->sapOrderService->sendOrderStatus($orderNumber, $sapStatus);
    
            if (!$setStatusResult) {
                $connection->rollbackTransaction();
                return false;
            }
            
            // отменяем заказ
            $cancelResult = (new \CSaleOrder)->cancelOrder($orderId, BaseEntity::BITRIX_TRUE, '');

            if ($cancelResult) {
                $order->setField('STATUS_ID', $newStatus);
                $saveResult = $order->save();
            } else {
                $connection->rollbackTransaction();
                return false;
            }

            if (!$saveResult->isSuccess()) {
                $connection->rollbackTransaction();
                return false;
            }
            
            $connection->commitTransaction();
        } catch (\Exception $e) {
            $connection->rollbackTransaction();
            return false;
        }

        if ($sendEmail) {
            \CEvent::Send('ADMIN_EMAIL_AFTER_ORDER_CANCEL', ['s1'], ['ORDER_NUMBER' => $order->getField('ACCOUNT_NUMBER')]);
        }

        return true;
    }

    /**
     * Продление заказа
     *
     * @param $orderId
     * @return mixed
     * @throws OrderExtendException
     * @throws SqlQueryException
     */
    public function extendOrder($orderId): bool
    {
        // ищем заказ
        try {
            $order = $this->getOrderById($orderId);
        } catch (\Exception $e) {
            throw new NotFoundException('Заказ не найден');
        }

        // валидация пользователя
        $userId = $order->getField('USER_ID');

        if (!$userId) {
            throw new OrderExtendException('Вы не можете продлить срок хранения для этого заказа');
        }

        try {
            if ((int)$userId !== $this->userProvider->getCurrentUserId()) {
                throw new OrderExtendException('Вы не можете продлить срок хранения для этого заказа');
            }
        } catch (NotAuthorizedException $e) {
            throw new OrderExtendException('Вы не можете продлить срок хранения для этого заказа');
        }

        // валидация статуса
        $statusId = $order->getField('STATUS_ID');

        if (!$statusId || in_array($statusId, PersonalOrderService::STATUS_FINAL, true) || (in_array($statusId, PersonalOrderService::STATUS_CANCEL, true))) {
            return false;
        }

        // формируем новый статус в зависимости от службы доставки
        $deliveryId = $order->getField('DELIVERY_ID');

        if (!$deliveryId) {
            throw new OrderExtendException('Не найдена служба доставки для заказа');
        }

        try {
            $deliveryCode = $this->deliveryService->getDeliveryCodeById($deliveryId);
            if ($this->deliveryService->isPickupCode($deliveryCode)) {
                $newStatus = OrderStatus::STATUS_PICKUP_EXTEND;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new OrderExtendException('Не найдена служба доставки для заказа');
        }

        $connection = BitrixApplication::getConnection();

        $connection->startTransaction();

        try {
            $order->setField('STATUS_ID', $newStatus);
            $saveResult = $order->save();

            if (!$saveResult->isSuccess()) {
                $connection->rollbackTransaction();
                return false;
            }

            // todo изначально хотели передавать в сап через файл, а сейчас хотят через АПИ
//            $sapStatus = StatusService::STATUS_PICKUP_EXTEND;
//            $this->sapOrderService->sendOrderStatus($order, $sapStatus);

            $connection->commitTransaction();
        } catch (\Exception $e) {
            $connection->rollbackTransaction();
            return false;
        }

        return true;
    }


    /**
     * @param Order $order
     * @param string $code
     *
     * @return string
     * @throws ArgumentException
     * @throws NotImplementedException
     */
    public function getPropertyValueByCode(Order $order, string $code): string
    {
        $propertyValue = BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), $code);

        return $propertyValue ? ($propertyValue->getValue() ?? '') : '';
    }
}
