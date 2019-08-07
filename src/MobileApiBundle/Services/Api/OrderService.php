<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\UserMessageException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DpdPickupResult;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\MobileApiBundle\Collection\BasketProductCollection;
use FourPaws\MobileApiBundle\Dto\Object\Basket\Product;
use FourPaws\MobileApiBundle\Dto\Object\City;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryTime;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryVariant;
use FourPaws\MobileApiBundle\Dto\Object\Detailing;
use FourPaws\MobileApiBundle\Dto\Object\Order;
use FourPaws\MobileApiBundle\Dto\Object\OrderCalculate;
use FourPaws\MobileApiBundle\Dto\Object\OrderHistory;
use FourPaws\MobileApiBundle\Dto\Object\OrderParameter;
use FourPaws\MobileApiBundle\Dto\Object\OrderStatus;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use FourPaws\MobileApiBundle\Dto\Object\PriceWithQuantity;
use FourPaws\MobileApiBundle\Dto\Request\PaginationRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserCartOrderRequest;
use FourPaws\MobileApiBundle\Exception\BonusSubtractionException;
use FourPaws\MobileApiBundle\Exception\OrderNotFoundException;
use FourPaws\MobileApiBundle\Exception\ProductsAmountUnavailableException;
use FourPaws\PersonalBundle\Entity\OrderItem;
use FourPaws\MobileApiBundle\Services\Api\BasketService as ApiBasketService;
use FourPaws\PersonalBundle\Exception\OrderSubscribeException;
use FourPaws\PersonalBundle\Service\BonusService as AppBonusService;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponStorageInterface;
use FourPaws\SaleBundle\Service\BasketService as AppBasketService;
use FourPaws\PersonalBundle\Entity\OrderStatusChange;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\SaleBundle\Exception\OrderStorageSaveException;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\SaleBundle\Service\OrderService as AppOrderService;
use FourPaws\SaleBundle\Enum\OrderStorage as OrderStorageEnum;
use FourPaws\PersonalBundle\Service\OrderService as PersonalOrderService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\UserService as AppUserService;
use FourPaws\MobileApiBundle\Services\Api\StoreService as ApiStoreService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Entity\Order as OrderEntity;
use FourPaws\SaleBundle\Service\OrderSplitService;
use FourPaws\DeliveryBundle\Service\DeliveryService as AppDeliveryService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService as AppOrderSubscribeService;
use FourPaws\MobileApiBundle\Services\Api\ProductService as ApiProductService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use FourPaws\MobileApiBundle\Security\ApiToken;
use JMS\Serializer\Serializer;


class OrderService
{
    /** @var ApiBasketService */
    private $apiBasketService;

    /** @var ApiStoreService */
    private $apiStoreService;

    /** @var OrderStorageService */
    private $orderStorageService;

    /** @var AppOrderService */
    private $appOrderService;

    /** @var PersonalOrderService */
    private $personalOrderService;

    /** @var OrderSplitService */
    private $orderSplitService;

    /** @var AppUserService */
    private $appUserService;

    /** @var LocationService */
    private $locationService;

    /** @var AppDeliveryService */
    private $appDeliveryService;

    /** @var AppOrderSubscribeService */
    private $appOrderSubscribeService;

    /** @var Serializer */
    private $serializer;

    /** @var AppBasketService */
    private $appBasketService;

    /** @var ApiProductService */
    private $apiProductService;

    /** @var CouponStorageInterface */
    private $couponStorage;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var PersonalOffersService */
    private $personalOffersService;

    const DELIVERY_TYPE_COURIER = 'courier';
    const DELIVERY_TYPE_PICKUP = 'pickup';
    const DELIVERY_TYPE_DOSTAVISTA = 'dostavista';
    const DELIVERY_TYPE_DOBROLAP = 'dobrolap';

    public function __construct(
        ApiBasketService $apiBasketService,
        AppBasketService $appBasketService,
        OrderStorageService $orderStorageService,
        AppOrderService $appOrderService,
        PersonalOrderService $personalOrderService,
        AppUserService $appUserService,
        ApiStoreService $apiStoreService,
        LocationService $locationService,
        OrderSplitService $orderSplitService,
        AppDeliveryService $appDeliveryService,
        AppOrderSubscribeService $appOrderSubscribeService,
        ApiProductService $apiProductService,
        Serializer $serializer,
        CouponStorageInterface $couponStorage,
        TokenStorageInterface $tokenStorage,
        AppBonusService $appBonusService,
        PersonalOffersService $personalOffersService
    )
    {
        $this->apiBasketService = $apiBasketService;
        $this->appBasketService = $appBasketService;
        $this->apiStoreService = $apiStoreService;
        $this->orderStorageService = $orderStorageService;
        $this->appOrderService = $appOrderService;
        $this->personalOrderService = $personalOrderService;
        $this->appUserService = $appUserService;
        $this->locationService = $locationService;
        $this->orderSplitService = $orderSplitService;
        $this->appDeliveryService = $appDeliveryService;
        $this->appOrderSubscribeService = $appOrderSubscribeService;
        $this->apiProductService = $apiProductService;
        $this->serializer = $serializer;
        $this->couponStorage = $couponStorage;
        $this->appBonusService = $appBonusService;
        $this->tokenStorage = $tokenStorage;
        $this->personalOffersService = $personalOffersService;
    }

    /**
     * @param PaginationRequest $paginationRequest
     * @return ArrayCollection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    public function getList($paginationRequest)
    {
        $orders = $this->getUserOrders($paginationRequest);
        // toDo подписка на заказ
        // $user = $this->appUserService->getCurrentUser();
        // $subscriptions = $this->appOrderSubscribeService->getSubscriptionsByUser($user->getId());
        return $orders->map(function (OrderEntity $order) /*use($subscriptions)*/ {
            /*
            $orderId = $order->getId();
            $subscription = $subscriptions->filter(function(OrderSubscribe $subscription) use($orderId) {
                return $subscription->getOrderId() === $orderId;
            })->current();
            */
            return $this->toApiFormat($order/*, $subscription*/);
        });
    }

    /**
     * @param int $orderId
     * @return Order
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Exception
     */
    public function getOneById(int $orderId)
    {
        $order = $this->personalOrderService->getOrderById($orderId);
        return $this->toApiFormat($order);
    }

    /**
     * @param int $orderNumber
     * @return Order
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Exception
     */
    public function getOneByNumber(int $orderNumber)
    {
        $order = $this->personalOrderService->getOrderByNumber($orderNumber);
        return $this->toApiFormat($order);
    }

    /**
     * @param int $orderNumber
     * @return Order
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Exception
     */
    public function getOneByNumberForCurrentUser(int $orderNumber)
    {
        $user = $this->appUserService->getCurrentUser();
        $order = $this->personalOrderService->getUserOrderByNumber($user, $orderNumber);
        if (!$order) {
            throw new OrderNotFoundException();
        }
        return $this->toApiFormat($order);
    }

    /**
     * @param int $orderNumber
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getHistoryForCurrentUser(int $orderNumber)
    {
        $user = $this->appUserService->getCurrentUser();
        $order = $this->personalOrderService->getUserOrderByNumber($user, $orderNumber);
        return $this->personalOrderService->getOrderStatuses($order)->map(function($status) {
            /** @var $status OrderStatusChange */
            $dateChangeStmp = $status->getDateCreate()->getTimestamp();
            $dateChange = (new \DateTime())->setTimestamp($dateChangeStmp);
            $status = (new OrderStatus())
                ->setCode($status->getOrderStatus()->getId())
                ->setTitle($status->getOrderStatus()->getName());
            return (new OrderHistory())
                ->setStatus($status)
                ->setDateChange($dateChange);
        })->toArray();
    }

    /**
     * @param OrderEntity $order
     * @param OrderSubscribe|null $subscription
     * @param array $text
     * @return Order
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\AppBundle\Exception\NotFoundException
     */
    protected function toApiFormat(OrderEntity $order, OrderSubscribe $subscription = null, $text = [])
    {
        if ($subscription) {
            // toDo подписка на заказ
        }
        $orderItems = $order->getItems();

        $dateInsert = (new \DateTime())->setTimestamp($order->getDateInsert()->getTimestamp());

        $status = (new OrderStatus())
            ->setTitle($order->getStatus())
            ->setCode($order->getStatusId());

        $response = new Order();

        if (!empty($order->getAccountNumber()) && $orderItems) {
            $basketProducts = $this->getBasketProducts($orderItems);

            $closedOrderStatuses = $this->personalOrderService->getClosedOrderStatuses();
            $currentMinusMonthDate = (new \DateTime)->modify('-1 month');
            $orderDateUpdate = \DateTime::createFromFormat('d.m.Y H:i:s', $order->getDateUpdate()->toString());
            $isCompleted = $orderDateUpdate < $currentMinusMonthDate || in_array($order->getStatusId(), $closedOrderStatuses, true);

            $response
                ->setId($order->getAccountNumber())
                ->setDateFormat($dateInsert)
                // ->setReviewEnabled($order->) // toDo reviews выбираются из таблички opros_checks, поля opros_4, opros_5, opros_8
                ->setStatus($status)
                ->setCompleted($isCompleted)
                ->setPaid($order->isPayed())
                ->setCartParam($this->getOrderParameter($basketProducts, $order, $text))
                ->setCartCalc($this->getOrderCalculate($basketProducts, false, 0, $order));
        }

        return $response;
    }

    /**
     * @param PaginationRequest $paginationRequest
     * @return ArrayCollection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    public function getUserOrders($paginationRequest)
    {
        $user = $this->appUserService->getCurrentUser();
        return $this->personalOrderService->getUserOrders($user, $paginationRequest->getPage(), $paginationRequest->getCount());
    }

    /**
     * @param BasketProductCollection $basketProducts
     * @param null $order
     * @param array $text
     * @return OrderParameter
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\NotFoundException
     */
    public function getOrderParameter(BasketProductCollection $basketProducts, $order = null, $text = [])
    {
        $orderParameter = (new OrderParameter())
            ->setProducts($basketProducts->getValues());

        try {
            $userId = $this->appUserService->getCurrentUserId();
        } /** @noinspection BadExceptionsProcessingInspection */
        catch (NotAuthorizedException $e) {
            $userId = null;
        }

        $basket = $this->appBasketService->getBasket();
        // привязывать к заказу нужно для расчета скидок
        if (null === $bxOrder = $basket->getOrder()) {
            $bxOrder = \Bitrix\Sale\Order::create(SITE_ID, $userId);
            $bxOrder->setBasket($basket);
            // но иногда он так просто не запускается
            if (!Manager::isExtendCalculated()) {
                $bxOrder->doFinalAction(true);
            }
        }
        if ($possibleGiftGroups = array_values(Gift::getPossibleGiftGroups($bxOrder))) {
            foreach ($possibleGiftGroups as &$possibleGiftGroup) {
                $possibleGiftGroup = current($possibleGiftGroup);
                if (!empty($possibleGiftGroup['list'])) {
                    $offers = (new OfferQuery())->withFilter(['=ID' => $possibleGiftGroup['list']])->exec();
                    foreach ($offers as $offer) {
                        $product = $offer->getProduct();
                        $possibleGiftGroup['goods'][] = $this->apiProductService->convertToShortProduct($product, $offer);
                    }
                }
                unset($possibleGiftGroup['list']);
            }
        }

        $orderParameter->gifts = $possibleGiftGroups;

        if ($order) {
            $orderParameter
                ->setName($order->getPropValue('NAME'))
                ->setPhone($order->getPropValue('PHONE'))
                ->setEmail($order->getPropValue('EMAIL'))
                ->setAltPhone($order->getPropValue('PHONE_ALT'))
                ->setDiscountCardNumber($order->getPropValue('DISCOUNT_CARD'))
                ->setStreet($order->getPropValue('STREET'))
                ->setHouse($order->getPropValue('HOUSE'))
                ->setBuilding($order->getPropValue('BUILDING'))
                ->setPorch($order->getPropValue('PORCH'))
                ->setFloor($order->getPropValue('FLOOR'))
                ->setApartment($order->getPropValue('APARTMENT'))
                ->setComment($order->getBitrixOrder()->getField('USER_DESCRIPTION') ?: '')
                ->setDeliveryPlaceCode($order->getPropValue('DELIVERY_PLACE_CODE'))
                ->setText($text)
                ->setActiveDobrolap(new \DateTime() <= new \DateTime('2019-08-30 23:59:59'))
                /** значение может меняться автоматически, @see \FourPaws\SaleBundle\Service\OrderService::updateCommWayProperty  */
                // ->setCommunicationWay($order->getPropValue('COM_WAY'))
            ;

            if($order->getPropValue('SUBSCRIBE_ID') > 0){
                $subscribeId = (int)$order->getPropValue('SUBSCRIBE_ID');
                $orderSubscribe = $this->appOrderSubscribeService->getById($subscribeId);

                $orderParameter
                    ->setSubscribe(true)
                    ->setSubscribeFrequency($orderSubscribe->getFrequency())
                    ->setPayWithBonus($orderSubscribe->isPayWithbonus() ? 1 : 0)
                ;
            }

            try {
                $deliveryCode = $this->appDeliveryService->getDeliveryCodeById($order->getDeliveryId());
            } catch (NotFoundException $e) {
                $logger = LoggerFactory::create('orderParameter');
                $logger->error(__METHOD__ . ' error. deliveryId: ' . $order->getDeliveryId() . '. userId: ' . $userId . '. orderId: ' . $order->getId() . '. Exception. : ' . $e->getMessage() . '. ' . $e->getTraceAsString());
            }
            if (isset($deliveryCode))
            {
                switch ($deliveryCode) {
                    case in_array($deliveryCode, DeliveryService::DELIVERY_CODES):
                        $orderParameter->setDeliveryType(self::DELIVERY_TYPE_COURIER);
                        $date = $order->getDateDelivery() . ' ' .  $order->getPropValue('DELIVERY_INTERVAL');
                        $orderParameter->setDeliveryDateTimeText($date);
                        break;
                    case in_array($deliveryCode, DeliveryService::PICKUP_CODES):
                        $orderParameter->setDeliveryType(self::DELIVERY_TYPE_PICKUP);
                        break;
                }
            }

            if ($cityCode = $order->getPropValue('CITY_CODE')) {
                $isCitySet = false;
                if (intval($cityCode)) {
                    $location = $this->locationService->findLocationByCode($cityCode);
                    if ($location && $location['NAME']) {
                        $city = (new City())
                            ->setTitle($location['NAME'])
                            ->setId($location['CODE'])
                            ->setLongitude($location['LONGITUDE'])
                            ->setLatitude($location['LATITUDE'])
                            ->setPath([$location['PATH'][count($location['PATH']) - 1]['NAME']]);
                        $orderParameter->setCity($city);
                        $isCitySet = true;
                    }
                }

                if (!$isCitySet) {
                    $city = (new City())->setTitle($cityCode);
                    $orderParameter->setCity($city);
                }
            }

            $orderParameter->setAddressText(
                (($cityParameter = $orderParameter->getCity()) ? $cityParameter->getTitle() . ', '  : '')
                . $orderParameter->getStreet()
                . ' д.' . $orderParameter->getHouse()
                . ' ' . $orderParameter->getBuilding()
                . ($orderParameter->getPorch() ? ' подъезд ' . $orderParameter->getBuilding() : '')
                . ($orderParameter->getFloor() ? ' этаж ' . $orderParameter->getFloor() : '')
                . ($orderParameter->getApartment() ? ' кв. ' . $orderParameter->getApartment() : '')
            );

            $weight = 0;
            /** @var \FourPaws\PersonalBundle\Entity\OrderItem $orderItem */
            try {
                foreach ($order->getItems() as $orderItem) {
                    $weight += $orderItem->getWeight() * $orderItem->getQuantity();
                }
            } catch (\Exception $e) {
                // do nothing
            }

            $orderParameter->setGoodsInfo($this->apiProductService::getGoodsTitleForCheckout(
                $basketProducts->getTotalQuantity(),
                $weight,
                $basketProducts->getTotalPrice()->getActual()
            ));

        }

        return $orderParameter;
    }

    /**
     * @param BasketProductCollection $basketProducts
     * @param bool $isCourierDelivery
     * @param float $bonusSubtractAmount
     * @param OrderEntity|null $order
     * @param string $deliveryCode
     * @param float $deliveryPrice
     * @return OrderCalculate
     * @throws ApplicationCreateException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     */
    public function getOrderCalculate(
        BasketProductCollection $basketProducts,
        $isCourierDelivery = false,
        float $bonusSubtractAmount = 0,
        OrderEntity $order = null,
        string $deliveryCode = '',
        ?float $deliveryPrice = 0.0
    )
    {
        $basketPrice = $basketProducts->getTotalPrice();
        $basketPriceWithDiscount = $basketPrice->getActual();
        $basketPriceWithoutDiscount = $basketPrice->getOld();
        if ($basketPriceWithoutDiscount == 0 && $basketPriceWithDiscount) {
            $basketPriceWithoutDiscount = $basketPriceWithDiscount;
        }
        $basketPriceSubscribe = $basketPrice->getSubscribe();
        $deliveryPrice = 0;
        $bonusAddAmount = 0;

        if ($order) {
            // if there is an order
            $deliveryPrice = $order->getDelivery()->getPriceDelivery();
            $priceWithoutDiscount = $basketPriceWithDiscount;
            $priceSubscribe = $basketPriceSubscribe;
            try {
                $priceWithDiscount = $order->getItemsSum();
                $bonusSubtractAmount = $order->getBonusPay();
                $discount = max($priceWithoutDiscount - $priceWithDiscount, 0);
            } catch (\Exception $e) {
                // do nothing
            }

            $totalPriceWithDiscount = $order->getPrice() - $bonusSubtractAmount;
            $totalPriceWithoutDiscount = $basketPriceWithDiscount + $deliveryPrice;
            $totalPriceSubscribe = $basketPriceSubscribe + $deliveryPrice;
        } else {
            $priceWithDiscount = $basketPriceWithDiscount;
            $priceWithoutDiscount = $basketPriceWithoutDiscount;
            $priceSubscribe = $basketPriceSubscribe;
            $discount = $basketProducts->getDiscount();
            try {
                if ($isCourierDelivery) {
                    $deliveries = $this->orderStorageService->getDeliveries($this->orderStorageService->getStorage());
                    foreach ($deliveries as $calculationResult) {
                        if ($this->appDeliveryService->isDelivery($calculationResult)) {
                            $delivery = $calculationResult;
                            $deliveryPrice = $delivery->getPrice();
                        }
                    }
                }

                if ($deliveryCode) {
                    if (!$deliveries) {
                        $deliveries = $this->orderStorageService->getDeliveries($this->orderStorageService->getStorage());
                    }

                    foreach ($deliveries as $delivery) {
                        if ($delivery->getDeliveryCode() == $deliveryCode) {
                            $deliveryPrice = $delivery->getDeliveryPrice();
                            break;
                        }
                    }
                }
            } catch (\Exception $e) {
                // do nothing
            }
            // if method called from the basket and there is no order yet

            $totalPriceWithDiscount = $priceWithDiscount + $deliveryPrice;
            $totalPriceWithoutDiscount = $priceWithoutDiscount + $deliveryPrice;
            $totalPriceSubscribe = $priceSubscribe + $deliveryPrice;
            $bonusAddAmount = $basketProducts->getAmountBonus();

            if ($bonusSubtractAmount > 0) {
                try {
                    $user = $this->appUserService->getCurrentUser();
                    $bonusInfo = $this->appBonusService->getManzanaBonusInfo($user);
                    if ($bonusSubtractAmount > $bonusInfo->getActiveBonus()) {
                        throw new BonusSubtractionException('Указано некорректное количество бонусов для списания');
                    }
                    $storage = $this->orderStorageService->getStorage();
                    $storage->setBonus($bonusSubtractAmount);
                    $this->orderStorageService->updateStorage($storage);
                    $totalPriceWithDiscount -= $bonusSubtractAmount;
                    $totalPriceSubscribe -= $bonusSubtractAmount;
                } catch (BonusSubtractionException $e) {
                    throw new BonusSubtractionException($e->getMessage());
                } catch (\Exception $e) {
                    // do nothing
                }
            }
        }

        $totalPrice = (new Price())
            ->setActual($totalPriceWithDiscount)
            ->setOld($totalPriceWithoutDiscount)
            ->setSubscribe($totalPriceSubscribe);

        if ($deliveryPrice) {
            $totalPrice->setCourierPrice($deliveryPrice);
        }

        return (new OrderCalculate())
            ->setPriceDetails([
                (new Detailing())
                    ->setId('cart_price_old')
                    ->setTitle('Стоимость товаров без скидки')
                    ->setValue($priceWithoutDiscount),
                (new Detailing())
                    ->setId('cart_price')
                    ->setTitle('Стоимость товаров со скидкой')
                    ->setValue($priceWithDiscount),
                (new Detailing())
                    ->setId('discount')
                    ->setTitle('Скидка')
                    ->setValue($discount),
                (new Detailing())
                    ->setId('delivery')
                    ->setTitle('Стоимость доставки')
                    ->setValue($deliveryPrice),
                /*(new Detailing())
                    ->setId('cart_price_subscribe')
                    ->setTitle('Стоимость товаров по подписке на доставку')
                    ->setValue($priceSubscribe)*/
            ])
            ->setCardDetails([
                (new Detailing())
                    ->setId('bonus_add')
                    ->setTitle('Начислено')
                    ->setValue($bonusAddAmount),
                (new Detailing())
                    ->setId('bonus_sub')
                    ->setTitle('Списано')
                    ->setValue($bonusSubtractAmount),
            ])
            ->setTotalPrice(
                $totalPrice
            );
    }

    /**
     * @param $orderItems ArrayCollection
     * @return BasketProductCollection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getBasketProducts($orderItems): BasketProductCollection
    {
        $basketProducts = new BasketProductCollection();
        foreach ($orderItems as $orderItem) {
            if (!$orderItem) {
                continue;
            }
            /**
             * @var $orderItem OrderItem
             */
            $offer = OfferQuery::getById($orderItem->getProductId());
            if (!$offer) {
                continue;
            }
            $product = $this->apiBasketService->getBasketProduct(
                $orderItem->getId(),
                $offer,
                $orderItem->getQuantity()
            );
            $product->setPrices([
                (new PriceWithQuantity())
                    ->setQuantity($orderItem->getQuantity())
                    ->setPrice($product->getShortProduct()->getPrice())
            ]);
            $basketProducts->add($product);
        }
        return $basketProducts;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDeliveryVariants()
    {
        $deliveries = $this->orderStorageService->getDeliveries($this->orderStorageService->getStorage());
        $delivery   = null;
        $pickup     = null;
        $dostavista = null;
        $dobrolap   = null;
        foreach ($deliveries as $calculationResult) {
            // toDo убрать условие "&& !$calculationResult instanceof DpdPickupResult" после того как в мобильном приложении будет реализован вывод точек DPD на карте в чекауте
            if ($this->appDeliveryService->isInnerPickup($calculationResult) && !$calculationResult instanceof DpdPickupResult) {
                $pickup = $calculationResult;
            } elseif ($this->appDeliveryService->isInnerDelivery($calculationResult)) {
                $delivery = $calculationResult;
            } elseif ($this->appDeliveryService->isDostavistaDelivery($calculationResult)) {
                $dostavista = $calculationResult;
            } elseif ($this->appDeliveryService->isDobrolapDelivery($calculationResult)) {
                $dobrolap = $calculationResult;
            }
        }
        $courierDelivery = (new DeliveryVariant());
        $pickupDelivery = (new DeliveryVariant());
        $dostavistaDelivery = (new DeliveryVariant());
        $dobrolapDelivery = (new DeliveryVariant());

        if ($delivery) {
            $courierDelivery
                ->setAvailable(true)
                ->setDate(DeliveryTimeHelper::showTime($delivery))
                ->setPrice($delivery->getDeliveryPrice());
        }
        if ($pickup) {
            $pickupDelivery
                ->setAvailable(true)
                ->setDate(DeliveryTimeHelper::showTime(
                    $pickup,
                    [
                        'SHOW_TIME' => !$this->appDeliveryService->isDpdPickup($pickup),
                    ]
                ))
                ->setPrice($pickup->getDeliveryPrice());
        }
        if ($dostavista) {
            $avaliable = $this->checkDostavistaAvaliability($dostavista);

            $currentDate = new \DateTime();

            $deliveryDate = $dostavista->getDeliveryDate();

            $dostavistaDelivery
                ->setAvailable($avaliable)
                ->setPrice($dostavista->getDeliveryPrice())
                ->setShortDate('В течение 3 часов');

            if ($deliveryDate->format('d.m') == $currentDate->format('d.m')) {
                $dostavistaDelivery->setDate('Сегодня, ' . $deliveryDate->format('d.m.Y') . ' - в течение 3 часов с момента заказа');
            } else {
                $dostavistaDelivery->setDate(DeliveryTimeHelper::showTime($dostavista) . ' - в течение 3 часов с момента заказа');
            }
        }

        if ($dobrolap) {
            $dobrolapDelivery
                ->setAvailable(true)
                ->setPrice($dobrolap->getDeliveryPrice());
        }

        return [$courierDelivery, $pickupDelivery, $dostavistaDelivery, $dobrolapDelivery];
    }

    public function checkDostavistaAvaliability($dostavista)
    {
        $avaliable = true;

        // TODO: Доделать определение доступности после доработки апптеки
        /*$storage = $this->orderStorageService->getStorage();
        $lng = $storage->getLng();
        $lat = $storage->getLat();
        $avaliable = $this->isMKAD($lat, $lng);*/

        return $avaliable;
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function getDeliveryDetails()
    {
        /** @var DeliveryVariant $courierDelivery */
        /** @var DeliveryVariant $pickupDelivery */
        [$courierDelivery, $pickupDelivery, $dostavistaDelivery, $dobrolapDelivery] = $this->getDeliveryVariants();
        $result = [
            'pickup' => $pickupDelivery,
            'courier' => $courierDelivery,
        ];

        if ($dobrolapDelivery) {
            $result['dobrolap'] = [
                'available' => $dobrolapDelivery->getAvailable(),
                'description' => 'Ваш заказ будет доставлен в&nbsp;выбранный Вами приют для&nbsp;бездомных животных. После оплаты заказа вы получите сюрприз и памятный магнит.',
            ];
        }

        if ($courierDelivery->getAvailable()) {
            $basketProducts = $this->apiBasketService->getBasketProducts(true);
            $orderStorage = $this->orderStorageService->getStorage();
            $deliveries = $this->orderStorageService->getDeliveries($orderStorage);
            $delivery = null;
            $pickup = null;
            foreach ($deliveries as $calculationResult) {
                if ($this->appDeliveryService->isDelivery($calculationResult)) {
                    $delivery = $calculationResult;
                }
                if ($this->appDeliveryService->isPickup($calculationResult)) {
                    $pickup = $calculationResult;
                }
            }
            $selectedDelivery = $delivery;
            // не разделенный заказ
            $result['singleOrder'] = $this->getDeliveryCourierDetails($selectedDelivery, $basketProducts);
            // есть недоступные к курьерке товары (их нет на складе)
            $result['hasDelayedGoods'] = count($basketProducts) > count($result['singleOrder']['goods']);

            // разделенный заказ
            $result['canSplitOrder'] = $this->orderSplitService->canSplitOrder($selectedDelivery);
            $result['splitOrder'] = [];
            if ($result['canSplitOrder']) {
                [$splitResult1, $splitResult2] = $this->orderSplitService->splitOrder($orderStorage);
                $result['splitOrder'][] = $this->getDeliveryCourierDetails($splitResult1->getDelivery(), $basketProducts);
                $result['splitOrder'][] = $this->getDeliveryCourierDetails($splitResult2->getDelivery(), $basketProducts);
            }
            $orderStorage->setDeliveryId($selectedDelivery->getDeliveryId());

            // подписка на доставку
            $result['subscribeFrequencies'] = $this->getSubscribeFrequencies();
            if($pickup){
                $result['pickupRanges'] = $this->getPickupRanges($pickup, $basketProducts);
            }
        }
        return [
            'cartDelivery' => $result
        ];
    }

    public function getSubscribeFrequencies()
    {
        $result = [];
        $frequencies = $this->appOrderSubscribeService->getFrequencies();
        $mapping = [
            'ID'     => 'id',
            'VALUE'  => 'title',
            'XML_ID' => 'code',
        ];

        foreach($frequencies as $frequency){
            $item = [];
            foreach($frequency as $fieldCode => $fieldValue){
                if(isset($mapping[$fieldCode])){
                    $item[$mapping[$fieldCode]] = $fieldValue;
                }
            }
            $result[] = $item;
        }

        return $result;
    }

    protected function getDeliveryCourierDetails(CalculationResultInterface $delivery, BasketProductCollection $basketProducts)
    {
        $result = [];
        $deliveryResult = $delivery->getStockResult()->getOrderable();
        [$deliveryResultItems, $deliveryResultWeight] = $this->getOrderItemData($deliveryResult);
        foreach (array_keys($deliveryResultItems) as $offerId) {
            foreach ($basketProducts as $basketProduct) {
                /** @var $basketProduct Product */
                if ($offerId === $basketProduct->getShortProduct()->getId()) {
                    $result['goods'][] = $basketProduct;
                }
            }
        }
        $deliveryResultQuantity = $deliveryResult->getAmount();
        $deliveryResultPrice = $deliveryResult->getPrice();
        $orderTitle = $this->apiProductService::getGoodsTitleForCheckout($deliveryResultQuantity, $deliveryResultWeight, $deliveryResultPrice);
        $nextDeliveries = $this->appDeliveryService->getNextDeliveries($delivery, 10);
        $result['date'] = (FormatDate('j F', $nextDeliveries[0]->getDeliveryDate()->getTimestamp()));
        $result['title'] = $orderTitle;
        $result['price'] = (new Price())->setActual($deliveryResultPrice);
        $result['deliveryRanges'] = $this->getDeliveryRanges($nextDeliveries);
        return $result;
    }

    protected function getPickupRanges(CalculationResultInterface $pickup)
    {
        $dates = [];
        $deliveries = $this->appDeliveryService->getNextDeliveries($pickup, 10);
        foreach ($deliveries as $deliveryDateIndex => $delivery) {
            $day = FormatDate('d.m.Y l', $delivery->getDeliveryDate()->getTimestamp());
            $dates[] = (new DeliveryTime())
                ->setTitle($day)
                ->setDeliveryDateIndex($deliveryDateIndex);
        }
        return $dates;
    }

    public function getDeliveryRanges(array $deliveries)
    {
        $dates = [];
        foreach ($deliveries as $deliveryDateIndex => $delivery) {
            /** @var DeliveryResult $delivery */
            $deliveryDate = $delivery->getDeliveryDate();
            $intervals = $delivery->getAvailableIntervals();
            $day = FormatDate('d.m.Y l', $delivery->getDeliveryDate()->getTimestamp());
            if (!empty($intervals) && count($intervals)) {
                foreach ($intervals as $deliveryIntervalIndex => $interval) {
                    /** @var Interval $interval */
                    $dates[] = (new DeliveryTime())
                        ->setTitle($day . ' ' . $interval)
                        ->setDeliveryDateIndex($deliveryDateIndex)
                        ->setDeliveryIntervalIndex($deliveryIntervalIndex + 1)
                    ;
                }
            } else {
                $dates[] = (new DeliveryTime())
                    ->setTitle($day)
                    ->setDeliveryDate($deliveryDate)
                    ->setDeliveryDateIndex($deliveryDateIndex)
                ;
            }
        }
        return $dates;
    }

    public function getOrderItemData(StockResultCollection $stockResultCollection): array
    {
        $itemData    = [];
        $totalWeight = 0;
        /** @var StockResult $item */
        foreach ($stockResultCollection->getIterator() as $item) {
            $weight                         = $item->getOffer()->getCatalogProduct()->getWeight() * $item->getAmount();
            $offerId                        = $item->getOffer()->getId();
            $itemData[$offerId]['name']     = $item->getOffer()->getName();
            $itemData[$offerId]['quantity'] += $item->getAmount();
            $itemData[$offerId]['price']    += $item->getPrice();
            $itemData[$offerId]['weight']   += $weight;

            $totalWeight += $weight;
        }

        return [
            $itemData,
            $totalWeight,
        ];
    }

    /**
     * @param UserCartOrderRequest $userCartOrderRequest
     * @return Order[]
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws OrderStorageSaveException
     * @throws UserMessageException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \FourPaws\SaleBundle\Exception\DeliveryNotAvailableException
     * @throws \FourPaws\SaleBundle\Exception\OrderCreateException
     * @throws \FourPaws\SaleBundle\Exception\OrderSplitException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws OrderSubscribeException
     */
    public function createOrder(UserCartOrderRequest $userCartOrderRequest)
    {
        $cartParam = $userCartOrderRequest->getCartParam();
        $deliveryType = $cartParam->getDeliveryType();
        $cartParamArray = $this->serializer->toArray($cartParam);

        switch ($deliveryType) {
            //toDo DPD доставка
            case self::DELIVERY_TYPE_COURIER:
                $cartParamArray['delyveryType'] = $cartParamArray['split'] ? 'twoDeliveries' : ''; // have no clue why this param used
                $cartParamArray['deliveryTypeId'] = $this->appDeliveryService->getDeliveryIdByCode(DeliveryService::INNER_DELIVERY_CODE);
                //toDo доставка DPD должна определяться автоматически, в зависимости от зоны
                break;
            case self::DELIVERY_TYPE_PICKUP:
                $cartParamArray['deliveryTypeId'] = $this->appDeliveryService->getDeliveryIdByCode(DeliveryService::INNER_PICKUP_CODE);
                //toDo доставка DPD должна определяться автоматически, в зависимости от зоны
                break;
            case self::DELIVERY_TYPE_DOSTAVISTA:
                $cartParamArray['delyveryType'] = $cartParamArray['split'] ? 'twoDeliveries' : ''; // have no clue why this param used
                $cartParamArray['deliveryTypeId'] = $this->appDeliveryService->getDeliveryIdByCode(DeliveryService::DELIVERY_DOSTAVISTA_CODE);
                break;
            case self::DELIVERY_TYPE_DOBROLAP:
                $cartParamArray['delyveryType'] = $cartParamArray['split'] ? 'twoDeliveries' : ''; // have no clue why this param used
                $cartParamArray['deliveryTypeId'] = $this->appDeliveryService->getDeliveryIdByCode(DeliveryService::DOBROLAP_DELIVERY_CODE);
                $cartParamArray['shelter'] = $cartParam->getShelter();
                break;
        }
        $cartParamArray['deliveryId'] = $cartParamArray['deliveryTypeId'];
        $cartParamArray['comment1'] = $cartParamArray['comment'];
        $cartParamArray['comment2'] = $cartParamArray['secondComment'];
        $storage = $this->orderStorageService->getStorage();
        $this->couponStorage->save($storage->getPromoCode()); // because we can't use sessions we get promo code from the database, save it into session for current hit and creating order
        foreach (\FourPaws\SaleBundle\Enum\OrderStorage::STEP_ORDER as $step) {
            $this->orderStorageService->setStorageValuesFromArray($storage, $cartParamArray, $step);
        }

        // создание подписки на доставку
        if($cartParam->isSubscribe()){
            $storage->setSubscribe(true);
            $result = $this->appOrderSubscribeService->createSubscription($storage, $cartParamArray);
            if(!$result->isSuccess()){
                throw new OrderSubscribeException(implode("; ", $result->getErrorMessages()));
            }
            $storage->setSubscribeId($result->getData()['subscribeId']);
            $this->orderStorageService->updateStorage($storage, OrderStorageEnum::NOVALIDATE_STEP);
        }

        /**
         * @var ApiToken $token | null
         */
        $platform = false;
        $token = $this->tokenStorage->getToken();
        if ($token && $token instanceof ApiToken && $session = $token->getApiUserSession()) {
            $platform = $session->getPlatform();
        }

        $storage->setFromApp(true)->setFromAppDevice($platform);
        try {
            $order = $this->appOrderService->createOrder($storage);
        } catch (OrderCreateException $e) {
            if ($e->getMessage() === 'Basket is empty') {
                throw new ProductsAmountUnavailableException();
            }
        }
        $firstOrder = $this->personalOrderService->getOrderByNumber($order->getField('ACCOUNT_NUMBER'));

        $text = [];
        if ($deliveryType == self::DELIVERY_TYPE_DOBROLAP) {
            $href = new FullHrefDecorator('/static/build/images/content/dobrolap/dobrolap-logo.png');
            $mainIcon = $href->getFullPublicPath();

            $fantIcons = [];

            for ($i = 1; $i < 7; ++$i) {
                $href->setPath('/static/build/images/content/dobrolap/icons/dobrolap-' . $i . '.png');
                $fantIcons[] = $href->getFullPublicPath();
            }

            $text = [
                'title' => 'СПАСИБО ЧТО ВЫ ТВОРИТЕ ДОБРО ВМЕСТЕ С НАМИ!',
                'titleOrder' => 'Ваш заказ №#' . $order->getField('ACCOUNT_NUMBER') . '# оформлен',
                'description' => 'И будет доставлен в Приют' . $cartParam->getShelter(),
                'titleThank' => 'МЫ ГОВОРИМ ВАМ СПАСИБО!',
                'descriptionFirstThank' => 'В знак благодарности мы подготовили небольшой сюрприз фанты "Добролап" с приятными презентами',
                'descriptionSecondThank' => 'Также мы вложим в Ваш следующий заказ подарок - памятный магнит.',
                'titleNow' => 'А СЕЙЧАС',
                'descriptionNow' => 'Выберите для себя один из шести сюрпризов, тапнув на любой из них.',
                'mainIcon' => $mainIcon,
                'fantIcons' => $fantIcons,
            ];
        }

        // активация подписки на доставку
        if($cartParam->isSubscribe()){
            $this->appOrderSubscribeService->activateSubscription($storage, $order);
        }

        $response = [
            $this->toApiFormat($firstOrder, null, $text)
        ];
        if ($relatedOrderId = $firstOrder->getProperty('RELATED_ORDER_ID')) {
            $response[] = $this->getOneById($relatedOrderId->getValue());
        }
        return $response;
    }


    public function isMKAD($lat, $lng): bool
    {
        $vertices_x = array(
            37.842762,
            37.842789,
            37.842627,
            37.841828,
            37.841217,
            37.840175,
            37.83916,
            37.837121,
            37.83262,
            37.829512,
            37.831353,
            37.834605,
            37.837597,
            37.839348,
            37.833842,
            37.824787,
            37.814564,
            37.802473,
            37.794235,
            37.781928,
            37.771139,
            37.758725,
            37.747945,
            37.734785,
            37.723062,
            37.709425,
            37.696256,
            37.683167,
            37.668911,
            37.647765,
            37.633419,
            37.616719,
            37.60107,
            37.586536,
            37.571938,
            37.555732,
            37.545132,
            37.526366,
            37.516108,
            37.502274,
            37.49391,
            37.484846,
            37.474668,
            37.469925,
            37.456864,
            37.448195,
            37.441125,
            37.434424,
            37.42598,
            37.418712,
            37.414868,
            37.407528,
            37.397952,
            37.388969,
            37.383283,
            37.378369,
            37.374991,
            37.370248,
            37.369188,
            37.369053,
            37.369619,
            37.369853,
            37.372943,
            37.379824,
            37.386876,
            37.390397,
            37.393236,
            37.395275,
            37.394709,
            37.393056,
            37.397314,
            37.405588,
            37.416601,
            37.429429,
            37.443596,
            37.459065,
            37.473096,
            37.48861,
            37.5016,
            37.513206,
            37.527597,
            37.543443,
            37.559577,
            37.575531,
            37.590344,
            37.604637,
            37.619603,
            37.635961,
            37.647648,
            37.667878,
            37.681721,
            37.698807,
            37.712363,
            37.723636,
            37.735791,
            37.741261,
            37.764519,
            37.765992,
            37.788216,
            37.788522,
            37.800586,
            37.822819,
            37.829754,
            37.837148,
            37.838926,
            37.840004,
            37.840965,
            37.841576,
        );


        $vertices_y = array(
            55.774558,
            55.76522,
            55.755723,
            55.747399,
            55.739103,
            55.730482,
            55.721939,
            55.712203,
            55.703048,
            55.694287,
            55.68529,
            55.675945,
            55.667752,
            55.658667,
            55.650053,
            55.643713,
            55.637347,
            55.62913,
            55.623758,
            55.617713,
            55.611755,
            55.604956,
            55.599677,
            55.594143,
            55.589234,
            55.583983,
            55.578834,
            55.574019,
            55.571999,
            55.573093,
            55.573928,
            55.574732,
            55.575816,
            55.5778,
            55.581271,
            55.585143,
            55.587509,
            55.5922,
            55.594728,
            55.60249,
            55.609685,
            55.617424,
            55.625801,
            55.630207,
            55.641041,
            55.648794,
            55.654675,
            55.660424,
            55.670701,
            55.67994,
            55.686873,
            55.695697,
            55.702805,
            55.709657,
            55.718273,
            55.728581,
            55.735201,
            55.744789,
            55.75435,
            55.762936,
            55.771444,
            55.779722,
            55.789542,
            55.79723,
            55.805796,
            55.814629,
            55.823606,
            55.83251,
            55.840376,
            55.850141,
            55.858801,
            55.867051,
            55.872703,
            55.877041,
            55.881091,
            55.882828,
            55.884625,
            55.888897,
            55.894232,
            55.899578,
            55.90526,
            55.907687,
            55.909388,
            55.910907,
            55.909257,
            55.905472,
            55.901637,
            55.898533,
            55.896973,
            55.895449,
            55.894868,
            55.893884,
            55.889094,
            55.883555,
            55.877501,
            55.874698,
            55.862464,
            55.861979,
            55.850257,
            55.850383,
            55.844167,
            55.832707,
            55.828789,
            55.821072,
            55.811599,
            55.802781,
            55.793991,
            55.785017,
        );

        $res = $this->is_in_polygon(count($vertices_x), $vertices_y, $vertices_x, floatval($lat), floatval($lng));

        return $res;
    }

    private function is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)
    {
        $i = $j = $c = $point = 0;
        for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
            $point = $i;
            if( $point == $points_polygon )
                $point = 0;
            if ( (($vertices_y[$point]  >  $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
                ($longitude_x < ($vertices_x[$j] - $vertices_x[$point]) * ($latitude_y - $vertices_y[$point]) / ($vertices_y[$j] - $vertices_y[$point]) + $vertices_x[$point]) ) )
                $c = !$c;
        }
        return $c;
    }
}
