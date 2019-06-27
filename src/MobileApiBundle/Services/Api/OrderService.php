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
                ->setCartParam($this->getOrderParameter($basketProducts, $order))
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
        $delivery   = null;
        $pickup     = null;
        $dostavista = null;
        foreach ($deliveries as $calculationResult) {
            // toDo убрать условие "&& !$calculationResult instanceof DpdPickupResult" после того как в мобильном приложении будет реализован вывод точек DPD на карте в чекауте
            if ($this->appDeliveryService->isInnerPickup($calculationResult) && !$calculationResult instanceof DpdPickupResult) {
                $pickup = $calculationResult;
            } elseif ($this->appDeliveryService->isInnerDelivery($calculationResult)) {
                $delivery = $calculationResult;
            } elseif ($this->appDeliveryService->isDostavistaDelivery($calculationResult)) {
                $dostavista = $calculationResult;
            }
        }
        $courierDelivery = (new DeliveryVariant());
        $pickupDelivery = (new DeliveryVariant());
        $dostavistaDelivery = (new DeliveryVariant());

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
        if ($dostavista) {
            $avaliable = $this->checkDostavistaAvaliability($dostavista);

            $dostavistaDelivery
                ->setAvailable(true)
                ->setDate(DeliveryTimeHelper::showTime($dostavista));
        }

        return [$courierDelivery, $pickupDelivery, $dostavistaDelivery];
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
        [$courierDelivery, $pickupDelivery, $dostavistaDelivery] = $this->getDeliveryVariants();
        $result = [
            'pickup' => $pickupDelivery,
            'courier' => $courierDelivery,
            'dostavista' => $dostavistaDelivery,
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


    public function isMKAD($lat, $lng): bool
    {
        $vertices_x = [
            0 => 37.842762,
            1 => 37.842789,
            2 => 37.842627,
            3 => 37.841828,
            4 => 37.841217,
            5 => 37.840175,
            6 => 37.83916,
            7 => 37.837121,
            8 => 37.83262,
            9 => 37.829512,
            10 => 37.831353,
            11 => 37.834605,
            12 => 37.837597,
            13 => 37.839348,
            14 => 37.833842,
            15 => 37.824787,
            16 => 37.814564,
            17 => 37.802473,
            18 => 37.794235,
            19 => 37.781928,
            20 => 37.771139,
            21 => 37.758725,
            22 => 37.747945,
            23 => 37.734785,
            24 => 37.723062,
            25 => 37.709425,
            26 => 37.696256,
            27 => 37.683167,
            28 => 37.668911,
            29 => 37.647765,
            30 => 37.633419,
            31 => 37.616719,
            32 => 37.60107,
            33 => 37.586536,
            34 => 37.571938,
            35 => 37.555732,
            36 => 37.545132,
            37 => 37.526366,
            38 => 37.516108,
            39 => 37.502274,
            40 => 37.49391,
            41 => 37.484846,
            42 => 37.474668,
            43 => 37.469925,
            44 => 37.456864,
            45 => 37.448195,
            46 => 37.441125,
            47 => 37.434424,
            48 => 37.42598,
            49 => 37.418712,
            50 => 37.414868,
            51 => 37.407528,
            52 => 37.397952,
            53 => 37.388969,
            54 => 37.383283,
            55 => 37.378369,
            56 => 37.374991,
            57 => 37.370248,
            58 => 37.369188,
            59 => 37.369053,
            60 => 37.369619,
            61 => 37.369853,
            62 => 37.372943,
            63 => 37.379824,
            64 => 37.386876,
            65 => 37.390397,
            66 => 37.393236,
            67 => 37.395275,
            68 => 37.394709,
            69 => 37.393056,
            70 => 37.397314,
            71 => 37.405588,
            72 => 37.416601,
            73 => 37.429429,
            74 => 37.443596,
            75 => 37.459065,
            76 => 37.473096,
            77 => 37.48861,
            78 => 37.5016,
            79 => 37.513206,
            80 => 37.527597,
            81 => 37.543443,
            82 => 37.559577,
            83 => 37.575531,
            84 => 37.590344,
            85 => 37.604637,
            86 => 37.619603,
            87 => 37.635961,
            88 => 37.647648,
            89 => 37.667878,
            90 => 37.681721,
            91 => 37.698807,
            92 => 37.712363,
            93 => 37.723636,
            94 => 37.735791,
            95 => 37.741261,
            96 => 37.764519,
            97 => 37.765992,
            98 => 37.788216,
            99 => 37.788522,
            100 => 37.800586,
            101 => 37.822819,
            102 => 37.829754,
            103 => 37.837148,
            104 => 37.838926,
            105 => 37.840004,
            106 => 37.840965,
            107 => 37.841576,
        ];


        $vertices_y = [
            0 => 55.774558,
            1 => 55.76522,
            2 => 55.755723,
            3 => 55.747399,
            4 => 55.739103,
            5 => 55.730482,
            6 => 55.721939,
            7 => 55.712203,
            8 => 55.703048,
            9 => 55.694287,
            10 => 55.68529,
            11 => 55.675945,
            12 => 55.667752,
            13 => 55.658667,
            14 => 55.650053,
            15 => 55.643713,
            16 => 55.637347,
            17 => 55.62913,
            18 => 55.623758,
            19 => 55.617713,
            20 => 55.611755,
            21 => 55.604956,
            22 => 55.599677,
            23 => 55.594143,
            24 => 55.589234,
            25 => 55.583983,
            26 => 55.578834,
            27 => 55.574019,
            28 => 55.571999,
            29 => 55.573093,
            30 => 55.573928,
            31 => 55.574732,
            32 => 55.575816,
            33 => 55.5778,
            34 => 55.581271,
            35 => 55.585143,
            36 => 55.587509,
            37 => 55.5922,
            38 => 55.594728,
            39 => 55.60249,
            40 => 55.609685,
            41 => 55.617424,
            42 => 55.625801,
            43 => 55.630207,
            44 => 55.641041,
            45 => 55.648794,
            46 => 55.654675,
            47 => 55.660424,
            48 => 55.670701,
            49 => 55.67994,
            50 => 55.686873,
            51 => 55.695697,
            52 => 55.702805,
            53 => 55.709657,
            54 => 55.718273,
            55 => 55.728581,
            56 => 55.735201,
            57 => 55.744789,
            58 => 55.75435,
            59 => 55.762936,
            60 => 55.771444,
            61 => 55.779722,
            62 => 55.789542,
            63 => 55.79723,
            64 => 55.805796,
            65 => 55.814629,
            66 => 55.823606,
            67 => 55.83251,
            68 => 55.840376,
            69 => 55.850141,
            70 => 55.858801,
            71 => 55.867051,
            72 => 55.872703,
            73 => 55.877041,
            74 => 55.881091,
            75 => 55.882828,
            76 => 55.884625,
            77 => 55.888897,
            78 => 55.894232,
            79 => 55.899578,
            80 => 55.90526,
            81 => 55.907687,
            82 => 55.909388,
            83 => 55.910907,
            84 => 55.909257,
            85 => 55.905472,
            86 => 55.901637,
            87 => 55.898533,
            88 => 55.896973,
            89 => 55.895449,
            90 => 55.894868,
            91 => 55.893884,
            92 => 55.889094,
            93 => 55.883555,
            94 => 55.877501,
            95 => 55.874698,
            96 => 55.862464,
            97 => 55.861979,
            98 => 55.850257,
            99 => 55.850383,
            100 => 55.844167,
            101 => 55.832707,
            102 => 55.828789,
            103 => 55.821072,
            104 => 55.811599,
            105 => 55.802781,
            106 => 55.793991,
            107 => 55.785017,
        ];

        $points_polygon = count($vertices_x) - 1;  // number vertices - zero-based array
        $i = $j = $c = 0;
        for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
            if ( (($vertices_y[$i]  >  $lat != ($vertices_y[$j] > $lat)) &&
                ($lng < ($vertices_x[$j] - $vertices_x[$i]) * ($lat - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) )
                $c = !$c;
        }
        return $c;
    }
}
