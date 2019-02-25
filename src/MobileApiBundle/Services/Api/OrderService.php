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
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\Helpers\WordHelper;
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
use FourPaws\MobileApiBundle\Dto\Request\UserCartOrderRequest;
use FourPaws\PersonalBundle\Entity\OrderItem;
use FourPaws\MobileApiBundle\Services\Api\BasketService as ApiBasketService;
use FourPaws\PersonalBundle\Entity\OrderStatusChange;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\OrderStorageSaveException;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\SaleBundle\Service\OrderService as AppOrderService;
use FourPaws\PersonalBundle\Service\OrderService as PersonalOrderService;
use FourPaws\UserBundle\Service\UserService;
use FourPaws\MobileApiBundle\Services\Api\StoreService as ApiStoreService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Entity\Order as OrderEntity;
use FourPaws\SaleBundle\Service\OrderSplitService;
use FourPaws\DeliveryBundle\Service\DeliveryService as AppDeliveryService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService as AppOrderSubscribeService;
use JMS\Serializer\Serializer;

class OrderService
{
    /**
     * @var ApiBasketService;
     */
    private $apiBasketService;

    /**
     * @var ApiStoreService
     */
    private $apiStoreService;

    /**
     * @var OrderStorageService
     */
    private $orderStorageService;

    /**
     * @var AppOrderService
     */
    private $appOrderService;

    /**
     * @var PersonalOrderService
     */
    private $personalOrderService;

    /**
     * @var OrderSplitService $orderSplitService
     */
    private $orderSplitService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * @var AppDeliveryService
     */
    private $appDeliveryService;
    /**
     * @var AppOrderSubscribeService;
     */
    private $appOrderSubscribeService;

    /** @var Serializer */
    private $serializer;


    const DELIVERY_TYPE_COURIER = 'courier';
    const DELIVERY_TYPE_PICKUP = 'pickup';

    public function __construct(
        ApiBasketService $apiBasketService,
        OrderStorageService $orderStorageService,
        AppOrderService $appOrderService,
        PersonalOrderService $personalOrderService,
        UserService $userService,
        ApiStoreService $apiStoreService,
        LocationService $locationService,
        OrderSplitService $orderSplitService,
        AppDeliveryService $appDeliveryService,
        AppOrderSubscribeService $appOrderSubscribeService,
        Serializer $serializer
    )
    {
        $this->apiBasketService = $apiBasketService;
        $this->apiStoreService = $apiStoreService;
        $this->orderStorageService = $orderStorageService;
        $this->appOrderService = $appOrderService;
        $this->personalOrderService = $personalOrderService;
        $this->userService = $userService;
        $this->locationService = $locationService;
        $this->orderSplitService = $orderSplitService;
        $this->appDeliveryService = $appDeliveryService;
        $this->appOrderSubscribeService = $appOrderSubscribeService;
        $this->serializer = $serializer;
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
        // $user = $this->userService->getCurrentUser();
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
     * @param int $orderNumber
     * @return Order
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
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
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws \Exception
     */
    public function getOneByNumberForCurrentUser(int $orderNumber)
    {
        $user = $this->userService->getCurrentUser();
        $order = $this->personalOrderService->getUserOrderByNumber($user, $orderNumber);
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
        $user = $this->userService->getCurrentUser();
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
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     */
    protected function toApiFormat(OrderEntity $order, OrderSubscribe $subscription = null)
    {
        if ($subscription) {
            // toDo подписка на заказ
            // var_dump($subscription->getDeliveryFrequencyEntity()->getValue());
            // var_dump($subscription->getDeliveryTimeFormattedRu());
        }
        $orderItems = $order->getItems();
        $basketProducts = $this->getBasketProducts($orderItems);

        $dateInsert = (new \DateTime())->setTimestamp($order->getDateInsert()->getTimestamp());

        $status = (new OrderStatus())
            ->setTitle($order->getStatus())
            ->setCode($order->getStatusId());

        return (new Order())
            ->setId($order->getAccountNumber())
            ->setDateFormat($dateInsert)
            // ->setReviewEnabled($order->) // toDo reviews выбираются из таблички opros_checks, поля opros_4, opros_5, opros_8
            ->setStatus($status)
            ->setCompleted($order->isClosed())
            ->setPaid($order->isPayed())
            ->setCartParam($this->getOrderParameter($basketProducts, $order))
            ->setCartCalc($this->getOrderCalculate($basketProducts));
    }

    /**
     * @return ArrayCollection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    public function getUserOrders()
    {
        $user = $this->userService->getCurrentUser();
        return $this->personalOrderService->getUserOrders($user);
    }

    /**
     * @param BasketProductCollection $basketProducts
     * @param OrderEntity $order
     * @return OrderParameter
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     */
    public function getOrderParameter(BasketProductCollection $basketProducts, $order = null)
    {
        $orderParameter = (new OrderParameter())
            ->setProducts($basketProducts->getValues());

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
                ->setComment($order->getBitrixOrder()->getField('USER_DESCRIPTION'))
                // ->setCommunicationWay($order->getPropValue('COM_WAY')) // значение может меняться автоматически, см. FourPaws\SaleBundle\Service\OrderService::updateCommWayProperty
            ;
            $deliveryCode = $this->appDeliveryService->getDeliveryCodeById($order->getDeliveryId());
            switch ($deliveryCode) {
                case in_array($deliveryCode, DeliveryService::DELIVERY_CODES):
                    $orderParameter->setDeliveryType(self::DELIVERY_TYPE_COURIER);
                    $orderParameter->setDeliveryDateText($order->getDateDelivery());
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
        }

        return $orderParameter;
    }

    /**
     * @param BasketProductCollection $basketProducts
     * @param float $bonusSubtractAmount
     * @return OrderCalculate
     */
    public function getOrderCalculate(BasketProductCollection $basketProducts, float $bonusSubtractAmount = 0)
    {
        $deliveryPrice = 0;
        try {
            $storage = $this->orderStorageService->getStorage();
            $deliveryPrice = $this->orderStorageService->getSelectedDelivery($storage)->getPrice();
        } catch (ArgumentException $e) {
        } catch (NotSupportedException $e) {
        } catch (ObjectNotFoundException $e) {
        } catch (UserMessageException $e) {
        } catch (ApplicationCreateException $e) {
        } catch (NotFoundException $e) {
        } catch (\FourPaws\StoreBundle\Exception\NotFoundException $e) {
        } catch (OrderStorageSaveException $e) {
            $deliveryPrice = 0;
        }

        $orderCalculate = (new OrderCalculate())
            ->setTotalPrice($basketProducts->getTotalPrice())
            ->setPriceDetails($basketProducts->getPriceDetails($deliveryPrice))
            ->setCardDetails([
                (new Detailing())
                    ->setId('bonus_add')
                    ->setTitle('Начислено')
                    ->setValue(0),
                (new Detailing())
                    ->setId('bonus_sub')
                    ->setTitle('Списано')
                    ->setValue($bonusSubtractAmount),
            ]);

        return $orderCalculate;
    }

    /**
     * @param $orderItems ArrayCollection
     * @return BasketProductCollection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getBasketProducts($orderItems): BasketProductCollection
    {
        $products = [];
        foreach ($orderItems as $orderItem) {
            /**
             * @var $orderItem OrderItem
             */
            $offer = OfferQuery::getById($orderItem->getProductId());
            $products[] = $this->apiBasketService->getBasketProduct(
                $orderItem->getId(),
                $offer,
                $orderItem->getQuantity()
            );
        }
        return new BasketProductCollection($products);
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Sale\UserMessageException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getDeliveryVariants()
    {
        $deliveries = $this->orderStorageService->getDeliveries(new OrderStorage());
        $delivery = null;
        $pickup   = null;
        foreach ($deliveries as $calculationResult) {
            if ($this->appDeliveryService->isPickup($calculationResult)) {
                $pickup = $calculationResult;
            } elseif ($this->appDeliveryService->isDelivery($calculationResult)) {
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

    public function getDeliveryDetails()
    {
        [$courierDelivery, $pickupDelivery] = $this->getDeliveryVariants();
        $result = [
            'pickup' => $pickupDelivery,
            'courier' => $courierDelivery,
        ];
        $basketProducts = $this->apiBasketService->getBasketProducts();
        $orderStorage = (new OrderStorage());
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

        // итоговые суммы
        $result['cart_calc'] = $this->getOrderCalculate($basketProducts);
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
        $orderTitle = $deliveryResultQuantity
            . ' '  . WordHelper::declension($deliveryResultQuantity, ['товар', 'товара', 'товаров'])
            . '(' . WordHelper::showWeight($deliveryResultWeight, true) . ') '
            . 'на сумму ' .  CurrencyHelper::formatPrice($deliveryResultPrice, false);
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
            $dayOfTheWeek = FormatDate('l', $delivery->getDeliveryDate()->getTimestamp());
            if (!empty($intervals) && count($intervals)) {
                foreach ($intervals as $deliveryIntervalIndex => $interval) {
                    /** @var Interval $interval */
                    $dates[] = (new DeliveryTime())
                        ->setTitle($dayOfTheWeek . ' ' . $interval)
                        ->setDeliveryDate($deliveryDate)
                        ->setDeliveryDateIndex($deliveryDateIndex)
                        ->setDeliveryIntervalIndex($deliveryIntervalIndex)
                    ;
                }
            } else {
                $dates[] = (new DeliveryTime())
                    ->setTitle($dayOfTheWeek)
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
     * @return Order
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
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \FourPaws\SaleBundle\Exception\DeliveryNotAvailableException
     * @throws \FourPaws\SaleBundle\Exception\OrderCreateException
     * @throws \FourPaws\SaleBundle\Exception\OrderSplitException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function createOrder(UserCartOrderRequest $userCartOrderRequest)
    {
        $cartParam = $userCartOrderRequest->getCartParam();
        $deliveryType = $cartParam->getDeliveryType();
        $cartParamArray = $this->serializer->toArray($cartParam);
        switch ($deliveryType) {
            //toDo DPD доставка
            case self::DELIVERY_TYPE_COURIER:
                $cartParamArray['deliveryId'] = DeliveryService::INNER_DELIVERY_CODE;
                //toDo доставка DPD должна определяться автоматически, в зависимости от зоны
                break;
            case self::DELIVERY_TYPE_PICKUP:
                $cartParamArray['deliveryId'] = DeliveryService::INNER_PICKUP_CODE;
                //toDo доставка DPD должна определяться автоматически, в зависимости от зоны
                break;
        }
        $storage = $this->orderStorageService->getStorage();
        foreach (\FourPaws\SaleBundle\Enum\OrderStorage::STEP_ORDER as $step) {
            $this->orderStorageService->setStorageValuesFromArray($storage, $cartParamArray, $step);
        }
        $storage->setFromApp(true);
        $order = $this->appOrderService->createOrder($storage);
        return $this->getOneByNumber($order->getField('ACCOUNT_NUMBER'));
    }
}
