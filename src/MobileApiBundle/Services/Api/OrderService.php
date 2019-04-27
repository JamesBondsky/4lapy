<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\UserMessageException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Query\OfferQuery;
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
use FourPaws\MobileApiBundle\Dto\Request\UserCartOrderRequest;
use FourPaws\MobileApiBundle\Exception\BonusSubtractionException;
use FourPaws\MobileApiBundle\Exception\OrderNotFoundException;
use FourPaws\MobileApiBundle\Exception\ProductsAmountUnavailableException;
use FourPaws\PersonalBundle\Entity\OrderItem;
use FourPaws\MobileApiBundle\Services\Api\BasketService as ApiBasketService;
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

    const DELIVERY_TYPE_COURIER = 'courier';
    const DELIVERY_TYPE_PICKUP = 'pickup';

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
        AppBonusService $appBonusService
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
    }

    /**
     * @return ArrayCollection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    public function getList()
    {
        $orders = $this->getUserOrders();
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
     * @param OrderSubscribe $subscription
     * @return Order
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws OrderStorageSaveException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     */
    protected function toApiFormat(OrderEntity $order, OrderSubscribe $subscription = null)
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
            $response
                ->setId($order->getAccountNumber())
                ->setDateFormat($dateInsert)
                // ->setReviewEnabled($order->) // toDo reviews выбираются из таблички opros_checks, поля opros_4, opros_5, opros_8
                ->setStatus($status)
                ->setCompleted($order->isClosed())
                ->setPaid($order->isPayed())
                ->setCartParam($this->getOrderParameter($basketProducts, $order))
                ->setCartCalc($this->getOrderCalculate($basketProducts, false, 0, $order));
        }

        return $response;
    }

    /**
     * @return ArrayCollection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    public function getUserOrders()
    {
        $user = $this->appUserService->getCurrentUser();
        return $this->personalOrderService->getUserOrders($user, 1, 0);
    }

    /**
     * @param BasketProductCollection $basketProducts
     * @param OrderEntity $order
     * @return OrderParameter
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     */
    public function getOrderParameter(BasketProductCollection $basketProducts, $order = null)
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
                /** значение может меняться автоматически, @see \FourPaws\SaleBundle\Service\OrderService::updateCommWayProperty  */
                // ->setCommunicationWay($order->getPropValue('COM_WAY'))
            ;

            $deliveryCode = $this->appDeliveryService->getDeliveryCodeById($order->getDeliveryId());
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

            if ($cityCode = $order->getPropValue('CITY_CODE')) {
                if (intval($cityCode)) {
                    $location = $this->locationService->findLocationByCode($cityCode);
                    $city = (new City())
                        ->setTitle($location['NAME'])
                        ->setId($location['CODE'])
                        ->setLongitude($location['LONGITUDE'])
                        ->setLatitude($location['LATITUDE'])
                        ->setPath([$location['PATH'][count($location['PATH']) - 1]['NAME']]);
                    $orderParameter->setCity($city);
                } else {
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
     * @return OrderCalculate
     * @throws ApplicationCreateException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     */
    public function getOrderCalculate(
        BasketProductCollection $basketProducts,
        $isCourierDelivery = false,
        float $bonusSubtractAmount = 0,
        OrderEntity $order = null
    )
    {
        $basketPrice = $basketProducts->getTotalPrice();
        $basketPriceWithDiscount = $basketPrice->getActual();
        $basketPriceWithoutDiscount = $basketPrice->getOld();
        $deliveryPrice = 0;
        $bonusAddAmount = 0;

        if ($order) {
            // if there is an order
            $deliveryPrice = $order->getDelivery()->getPriceDelivery();
            $priceWithoutDiscount = $basketPriceWithDiscount;
            try {
                $priceWithDiscount = $order->getItemsSum();
                $bonusSubtractAmount = $order->getBonusPay();
                $discount = max($priceWithoutDiscount - $priceWithDiscount, 0);
            } catch (\Exception $e) {
                // do nothing
            }

            $totalPriceWithDiscount = $order->getPrice() - $bonusSubtractAmount;
            $totalPriceWithoutDiscount = $basketPriceWithDiscount + $deliveryPrice;
        } else {
            $priceWithDiscount = $basketPriceWithDiscount;
            $priceWithoutDiscount = $basketPriceWithoutDiscount;
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
            } catch (\Exception $e) {
                // do nothing
            }
            // if method called from the basket and there is no order yet

            $totalPriceWithDiscount = $priceWithDiscount + $deliveryPrice;
            $totalPriceWithoutDiscount = $priceWithoutDiscount + $deliveryPrice;
            $bonusAddAmount = $basketProducts->getTotalBonuses();

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
                } catch (BonusSubtractionException $e) {
                    throw new BonusSubtractionException($e->getMessage());
                } catch (\Exception $e) {
                    // do nothing
                }
            }
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
                    ->setValue($deliveryPrice)
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
                (new Price())
                    ->setActual($totalPriceWithDiscount)
                    ->setOld($totalPriceWithoutDiscount)
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
        $delivery = null;
        $pickup   = null;
        foreach ($deliveries as $calculationResult) {
            // toDo убрать условие "&& !$calculationResult instanceof DpdPickupResult" после того как в мобильном приложении будет реализован вывод точек DPD на карте в чекауте
            if ($this->appDeliveryService->isInnerPickup($calculationResult) && !$calculationResult instanceof DpdPickupResult) {
                $pickup = $calculationResult;
            } elseif ($this->appDeliveryService->isInnerDelivery($calculationResult)) {
                $delivery = $calculationResult;
            }
        }
        $courierDelivery = (new DeliveryVariant());
        $pickupDelivery = (new DeliveryVariant());

        if ($delivery) {
            $courierDelivery
                ->setAvailable(true)
                ->setDate(DeliveryTimeHelper::showTime($delivery));
        }
        if ($pickup) {
            $pickupDelivery
                ->setAvailable(true)
                ->setDate(DeliveryTimeHelper::showTime(
                    $pickup,
                    [
                        'SHOW_TIME' => !$this->appDeliveryService->isDpdPickup($pickup),
                    ]
                ));
        }

        return [$courierDelivery, $pickupDelivery];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDeliveryDetails()
    {
        /** @var DeliveryVariant $courierDelivery */
        /** @var DeliveryVariant $pickupDelivery */
        [$courierDelivery, $pickupDelivery] = $this->getDeliveryVariants();
        $result = [
            'pickup' => $pickupDelivery,
            'courier' => $courierDelivery,
        ];
        if ($courierDelivery->getAvailable()) {
            $basketProducts = $this->apiBasketService->getBasketProducts(true);
            $orderStorage = $this->orderStorageService->getStorage();
            $deliveries = $this->orderStorageService->getDeliveries($orderStorage);
            $delivery = null;
            foreach ($deliveries as $calculationResult) {
                if ($this->appDeliveryService->isDelivery($calculationResult)) {
                    $delivery = $calculationResult;
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
        }
        return [
            'cartDelivery' => $result
        ];
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

    protected function getDeliveryRanges(array $deliveries)
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
                        ->setDeliveryIntervalIndex($deliveryIntervalIndex)
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
        }
        $cartParamArray['deliveryId'] = $cartParamArray['deliveryTypeId'];
        $cartParamArray['comment1'] = $cartParamArray['comment'];
        $cartParamArray['comment2'] = $cartParamArray['secondComment'];
        $storage = $this->orderStorageService->getStorage();
        $this->couponStorage->save($storage->getPromoCode()); // because we can't use sessions we get promo code from the database, save it into session for current hit and creating order
        foreach (\FourPaws\SaleBundle\Enum\OrderStorage::STEP_ORDER as $step) {
            $this->orderStorageService->setStorageValuesFromArray($storage, $cartParamArray, $step);
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
        $response = [
            $this->toApiFormat($firstOrder)
        ];
        if ($relatedOrderId = $firstOrder->getProperty('RELATED_ORDER_ID')) {
            $response[] = $this->getOneById($relatedOrderId->getValue());
        }
        return $response;
    }
}
