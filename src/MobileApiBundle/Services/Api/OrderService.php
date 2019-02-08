<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\Helpers\WordHelper;
use FourPaws\MobileApiBundle\Collection\BasketProductCollection;
use FourPaws\MobileApiBundle\Dto\Object\City;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress;
use FourPaws\MobileApiBundle\Dto\Object\Detailing;
use FourPaws\MobileApiBundle\Dto\Object\Order;
use FourPaws\MobileApiBundle\Dto\Object\OrderCalculate;
use FourPaws\MobileApiBundle\Dto\Object\OrderHistory;
use FourPaws\MobileApiBundle\Dto\Object\OrderParameter;
use FourPaws\MobileApiBundle\Dto\Object\OrderStatus;
use FourPaws\MobileApiBundle\Dto\Request\UserCartOrderRequest;
use FourPaws\PersonalBundle\Entity\OrderItem;
use FourPaws\MobileApiBundle\Services\Api\BasketService as ApiBasketService;
use FourPaws\PersonalBundle\Entity\OrderStatusChange;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\SaleBundle\Service\OrderService as AppOrderService;
use FourPaws\PersonalBundle\Service\OrderService as PersonalOrderService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\UserService;
use FourPaws\MobileApiBundle\Services\Api\StoreService as ApiStoreService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Entity\Order as OrderEntity;
use FourPaws\SaleBundle\Service\OrderSplitService;
use FourPaws\DeliveryBundle\Service\DeliveryService as AppDeliveryService;

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

    public function __construct(
        ApiBasketService $apiBasketService,
        OrderStorageService $orderStorageService,
        AppOrderService $appOrderService,
        PersonalOrderService $personalOrderService,
        UserService $userService,
        ApiStoreService $apiStoreService,
        LocationService $locationService,
        OrderSplitService $orderSplitService,
        AppDeliveryService $appDeliveryService
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
        return $orders->map(function (OrderEntity $order) {
            return $this->toApiFormat($order);
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
     * @return Order
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    protected function toApiFormat(OrderEntity $order)
    {
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
     * @param bool|OrderEntity $order
     * @return OrderParameter
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getOrderParameter(BasketProductCollection $basketProducts, $order = false)
    {
        $orderParameter = (new OrderParameter())
            ->setProducts($basketProducts->getValues());

        if ($order) {
            $orderParameter
                ->setDeliveryPlace($this->getDeliveryAddress($order))
                ->setUserPhone($order->getPropValue('PHONE'))
                ->setExtraPhone($order->getPropValue('PHONE_ALT'))
                ->setCard($order->getPropValue('DISCOUNT_CARD'));
        }

        return $orderParameter;
    }

    /**
     * @param OrderEntity $order
     * @return DeliveryAddress
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getDeliveryAddress(OrderEntity $order)
    {
        $deliveryAddress = (new DeliveryAddress())
            ->setTitle($order->getPropValue('CITY'))
            ->setStreetName($order->getPropValue('STREET'))
            ->setHouse($order->getPropValue('HOUSE'))
            ->setFlat($order->getPropValue('APARTMENT'));
        $cityCode = $order->getPropValue('CITY_CODE');
        if ($cityCode && intval($cityCode)) {
            $location = $this->locationService->findLocationByCode($cityCode);
            $city = (new City())
                ->setTitle($location['NAME'])
                ->setId($location['CODE'])
                ->setLongitude($location['LONGITUDE'])
                ->setLatitude($location['LATITUDE'])
                ->setPath([$location['PATH'][count($location['PATH']) - 1]['NAME']]);
            $deliveryAddress->setCity($city);
        } else {
            $city = (new City())->setTitle($cityCode);
            $deliveryAddress->setCity($city);
        }
        return $deliveryAddress;
    }

    /**
     * @param BasketProductCollection $basketProducts
     * @param string $storeCode
     * @return OrderCalculate
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getOrderCalculate(BasketProductCollection $basketProducts, $storeCode = '')
    {

        $orderCalculate = (new OrderCalculate())
            ->setTotalPrice($basketProducts->getTotalPrice())
            ->setPriceDetails($basketProducts->getPriceDetails())
            ->setCardDetails([
                (new Detailing())
                    ->setId('bonus_add')
                    ->setTitle('Начислено')
                    ->setValue(0),
                (new Detailing())
                    ->setId('bonus_sub')
                    ->setTitle('Списано')
                    ->setValue(0),
            ]);

        if (strlen($storeCode)) {
            $orderCalculate
                ->setAvailableGoods($basketProducts->getAvailableInStore($storeCode))
                ->setNotAvailableGoods($basketProducts->getUnAvailableInStore($storeCode));
        }

        return $orderCalculate;
    }

    /**
     * @param $orderItems ArrayCollection
     * @return BasketProductCollection
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getBasketProducts($orderItems): BasketProductCollection
    {
        $products = [];
        foreach ($orderItems as $orderItem) {
            /**
             * @var $orderItem OrderItem
             */
            $products[] = $this->apiBasketService->getBasketProduct($orderItem->getId(), $orderItem->getProductId(), $orderItem->getQuantity());
        }
        return new BasketProductCollection($products);
    }

    public function getDeliveryVariants()
    {
        $orderStorage = (new OrderStorage());
        $selectedDelivery = $this->orderStorageService->getSelectedDelivery($orderStorage);
        $orderStorage->setDeliveryId($selectedDelivery->getDeliveryId());
        $splitResult1 = [];
        $splitResult2 = [];
        if ($this->orderSplitService->canSplitOrder($selectedDelivery)) {
            [$splitResult1, $splitResult2] = $this->orderSplitService->splitOrder($orderStorage);
        }

        $deliveryResult1 = $splitResult1->getDelivery()->getStockResult()->getOrderable();
        $deliveryResult2 = $splitResult2->getDelivery()->getStockResult()->getOrderable();
        [$deliveryResult1Items, $deliveryResult1Weight] = $this->getOrderItemData($deliveryResult1);
        [$deliveryResult2Items, $deliveryResult2Weight] = $this->getOrderItemData($deliveryResult2);

        $deliveryResult1Quantity = $deliveryResult1->getAmount();
        $deliveryResult1Price = $deliveryResult1->getPrice();
        $deliveryResult2Quantity = $deliveryResult2->getAmount();
        $deliveryResult2Price = $deliveryResult2->getPrice();


        $order1title = $deliveryResult1Quantity
            . ' '  . WordHelper::declension($deliveryResult1Quantity, ['товар', 'товара', 'товаров'])
            . '(' . WordHelper::showWeight($deliveryResult1Weight, true) . ') '
            . 'на сумму ' .  CurrencyHelper::formatPrice($deliveryResult1Price, false);

        $order2title = $deliveryResult2Quantity
            . ' '  . WordHelper::declension($deliveryResult2Quantity, ['товар', 'товара', 'товаров'])
            . '(' . WordHelper::showWeight($deliveryResult2Weight, true) . ')'
            . ' на сумму ' .  CurrencyHelper::formatPrice($deliveryResult2Price, false);

        // 1 заказ
        $nextDeliveries1 = $this->appDeliveryService->getNextDeliveries($splitResult1->getDelivery(), 10);
        var_dump(FormatDate('j F', $nextDeliveries1[0]->getDeliveryDate()->getTimestamp()));
        var_dump($order1title);
        var_dump($deliveryResult1Items);
        var_dump($deliveryResult1Price);
        $dates1 = [];
        foreach ($nextDeliveries1 as $i => $nextDelivery) {
            $date['date'] = FormatDate('l, d.m.Y', $nextDelivery->getDeliveryDate()->getTimestamp());
            $date['intervals'] = [];
            foreach ($nextDelivery->getAvailableIntervals() as $interval) {
                $date['intervals'][] = (string) $interval;
            }
            $dates1[] = $date;
        }
        var_dump($dates1);

        // 2 заказ
        $nextDeliveries2 = $this->appDeliveryService->getNextDeliveries($splitResult2->getDelivery(), 10);
        var_dump(FormatDate('j F', $nextDeliveries2[0]->getDeliveryDate()->getTimestamp()));
        var_dump($order2title);
        var_dump($deliveryResult2Items);
        var_dump($deliveryResult2Price);
        $dates2 = [];
        foreach ($nextDeliveries2 as $i => $nextDelivery) {
            $date['date'] = FormatDate('l, d.m.Y', $nextDelivery->getDeliveryDate()->getTimestamp());
            $date['intervals'] = [];
            foreach ($nextDelivery->getAvailableIntervals() as $interval) {
                $date['intervals'][] = (string) $interval;
            }
            $dates2[] = $date;
        }
        var_dump($dates2);

        // тотал
        var_dump($selectedDelivery->getStockResult()->getOrderable()->getPrice());
        die();
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
     * @param string $deliveryType
     * @return Order
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Sale\UserMessageException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \FourPaws\SaleBundle\Exception\DeliveryNotAvailableException
     * @throws \FourPaws\SaleBundle\Exception\OrderCreateException
     * @throws \FourPaws\SaleBundle\Exception\OrderSplitException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function createOrder(UserCartOrderRequest $userCartOrderRequest, string $deliveryType)
    {
        $cartParam = $userCartOrderRequest->getCartParam();
        $orderStorage = (new OrderStorage())
            ->setCurrentDate(new \DateTime())
            ->setDiscountCardNumber($cartParam->getCard())
            ->setName($cartParam->getFullName())
            ->setEmail($cartParam->getEmail())
            ->setPhone($cartParam->getUserPhone())
            ->setAltPhone($cartParam->getExtraPhone())
            ->setComment($cartParam->getComment());

        try {
            if ($userId = $this->userService->getCurrentUserId()) {
                $orderStorage->setUserId($userId);
            }
        } catch (NotAuthorizedException $e) {
            // do nothing
            // it's okay if user could be not authorized while making order
        }

        $paymentType = $cartParam->getPaymentType();
        if ($paymentType === 'cash') {
            $paymentId = 1;
        } else if (in_array($paymentType, ['cashless', 'applepay', 'android'])) {
            $paymentId = 3;
        } else {
            $paymentId = null;
        }

        if ($paymentId) {
            $orderStorage->setPaymentId($paymentId);
        }

        switch ($deliveryType) {
            case DeliveryService::INNER_DELIVERY_CODE:
                $orderStorage
                    ->setDeliveryId($cartParam->getDeliveryType())
                    ->setDeliveryInterval($cartParam->getDeliveryRangeId())
                    ->setCity($cartParam->getCity())
                    ->setStreet($cartParam->getStreet())
                    ->setHouse($cartParam->getHouse())
                    ->setBuilding($cartParam->getBuilding())
                    ->setApartment($cartParam->getApartment())
                ;
                // ->setDeliveryDate($userCartOrderRequest->getCartParam()->getDeliveryRangeDate())
                break;
            case DeliveryService::DPD_PICKUP_CODE:
                // toDo указать код магазина из которого нужно забрать товар?
                break;
            case DeliveryService::INNER_PICKUP_CODE:
                // toDo указать код магазина из которого нужно забрать товар?
                break;
        }

        $order = $this->appOrderService->createOrder($orderStorage);
        return $this->getOneByNumber($order->getField('ACCOUNT_NUMBER'));
    }
}
