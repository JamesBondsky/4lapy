<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\MobileApiBundle\Collection\BasketProductCollection;
use FourPaws\MobileApiBundle\Dto\Object\City;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress;
use FourPaws\MobileApiBundle\Dto\Object\Detailing;
use FourPaws\MobileApiBundle\Dto\Object\Order;
use FourPaws\MobileApiBundle\Dto\Object\OrderCalculate;
use FourPaws\MobileApiBundle\Dto\Object\OrderParameter;
use FourPaws\MobileApiBundle\Dto\Object\OrderStatus;
use FourPaws\MobileApiBundle\Dto\Request\UserCartOrderRequest;
use FourPaws\PersonalBundle\Entity\OrderItem;
use FourPaws\MobileApiBundle\Services\Api\BasketService as ApiBasketService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\SaleBundle\Service\OrderService as AppOrderService;
use FourPaws\PersonalBundle\Service\OrderService as PersonalOrderService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\UserService;
use FourPaws\MobileApiBundle\Services\Api\StoreService as ApiStoreService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Entity\Order as OrderEntity;

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
     * @var UserService
     */
    private $userService;

    /**
     * @var LocationService
     */
    private $locationService;

    public function __construct(
        ApiBasketService $apiBasketService,
        OrderStorageService $orderStorageService,
        AppOrderService $appOrderService,
        PersonalOrderService $personalOrderService,
        UserService $userService,
        ApiStoreService $apiStoreService,
        LocationService $locationService
    )
    {
        $this->apiBasketService = $apiBasketService;
        $this->apiStoreService = $apiStoreService;
        $this->orderStorageService = $orderStorageService;
        $this->appOrderService = $appOrderService;
        $this->personalOrderService = $personalOrderService;
        $this->userService = $userService;
        $this->locationService = $locationService;
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
     * @param int $orderId
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
    public function getOne(int $orderId)
    {
        $order = $this->personalOrderService->getOrderById($orderId);
        return $this->toApiFormat($order);
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
        return $this->getOne($order->getId());
    }
}
