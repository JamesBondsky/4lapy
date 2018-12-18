<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\LocationBundle\LocationService;
use FourPaws\MobileApiBundle\Dto\Object\City;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress;
use FourPaws\MobileApiBundle\Dto\Object\Detailing;
use FourPaws\MobileApiBundle\Dto\Object\OrderCalculate;
use FourPaws\MobileApiBundle\Dto\Object\OrderParameter;
use FourPaws\MobileApiBundle\Dto\Object\OrderStatus;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use FourPaws\MobileApiBundle\Dto\Request\OrderInfoRequest;
use FourPaws\MobileApiBundle\Dto\Request\OrderStatusHistoryRequest;
use FourPaws\MobileApiBundle\Dto\Response\OrderInfoResponse;
use FourPaws\MobileApiBundle\Dto\Response\OrderListResponse;
use FourPaws\MobileApiBundle\Dto\Response\OrderStatusHistoryResponse;
use FourPaws\PersonalBundle\Entity\Order;
use \FourPaws\MobileApiBundle\Dto\Object\Order as ApiObjectOrder;
use FourPaws\PersonalBundle\Service\OrderService;
use FourPaws\UserBundle\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class PushController
 * @package FourPaws\MobileApiBundle\Controller
 * @Security("has_role('REGISTERED_USERS')")
 */
class OrderController extends FOSRestController
{
    /**
     * @Rest\Get(path="/order_list_v2/")
     * @Rest\View()
     * @param OrderService $orderService
     * @param LocationService $locationService
     * @return OrderListResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    public function getOrderListAction(
        OrderService $orderService,
        LocationService $locationService
    )
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $orders = $orderService->getUserOrders($user)->getValues();
        $orders = array_map(function (Order $order) use ($locationService) {

            $basketPrice = 0;
            $basketDiscountPrice = 0;
            $orderItems = $order->getItems();
            /**
             * @var $orderItem \FourPaws\PersonalBundle\Entity\OrderItem
             */
            foreach ($orderItems as $orderItem) {
                $basketPrice += $orderItem->getQuantity() * $orderItem->getBasePrice();
                $basketDiscountPrice += $orderItem->getQuantity() * $orderItem->getPrice();
            }

            $dateInsert = (new \DateTime())->setTimestamp($order->getDateInsert()->getTimestamp());

            $status = (new OrderStatus())
                ->setTitle($order->getStatus())
                ->setCode($order->getStatusId());

            $price = (new Price())
                ->setActual($order->getPrice());

            $priceDetails = [
                (new Detailing())
                    ->setId('cart_price_old')
                    ->setTitle('Стоимость товаров без скидки')
                    ->setValue($order->getItemsSum()),
                (new Detailing())
                    ->setId('cart_price')
                    ->setTitle('Стоимость товаров со скидкой')
                    ->setValue($order->getItemsSum()),
                (new Detailing())
                    ->setId('discount')
                    ->setTitle('Скидка')
                    ->setValue(0), // toDo доделать скидку
                (new Detailing())
                    ->setId('delivery')
                    ->setTitle('Стоимость доставки')
                    ->setValue($order->getDelivery()->getPriceDelivery()),
            ];

            // toDo доделать начисление бонусов
            $cardDetails = [
                (new Detailing())
                    ->setId('bonus_add')
                    ->setTitle('Начислено')
                    ->setValue($order->getPropValue('BONUS_COUNT')),
                (new Detailing())
                    ->setId('bonus_sub')
                    ->setTitle('Списано')
                    ->setValue($order->getBonusPay()),
            ];

            $deliveryAddress = (new DeliveryAddress())
                ->setTitle($order->getPropValue('CITY'))
                ->setStreetName($order->getPropValue('STREET'))
                ->setHouse($order->getPropValue('HOUSE'))
                ->setFlat($order->getPropValue('APARTMENT'))
            ;
            $cityCode = $order->getPropValue('CITY_CODE');
            if ($cityCode && intval($cityCode)) {
                $location = $locationService->findLocationByCode($cityCode);
                $city = (new City())
                    ->setTitle($location['NAME'])
                    ->setId($location['CODE'])
                    ->setLongitude($location['LONGITUDE'])
                    ->setLatitude($location['LATITUDE'])
                    ->setPath([$location['PATH'][count($location['PATH']) - 1]['NAME']])
                ;
                $deliveryAddress->setCity($city);
            } else {
                $city = (new City())->setTitle($cityCode);
                $deliveryAddress->setCity($city);
            }

            $orderParameter = (new OrderParameter())
                ->setDeliveryPlace($deliveryAddress)
                ->setUserPhone($order->getPropValue('PHONE'))
                ->setExtraPhone($order->getPropValue('PHONE_ALT'))
                ->setCard($order->getPropValue('DISCOUNT_CARD'))
                ->setCardBonusUsed($order->getBonusPay()) // toDo сколько бонусов было списано на заказ
            ;
            $orderCalculate = (new OrderCalculate())
                ->setTotalPrice($price)
                ->setPriceDetails($priceDetails)
                ->setCardDetails($cardDetails)
            ;

            return (new ApiObjectOrder())
                ->setId($order->getId())
                ->setDateFormat($dateInsert)
                // ->setReviewEnabled($order->) // toDo reviews выбираются из таблички opros_checks, поля opros_4, opros_5, opros_8
                ->setStatus($status)
                ->setCompleted($order->isClosed())
                ->setPaid($order->isPayed())
                ->setCartParam($orderParameter)
                ->setCartCalc($orderCalculate);
}       , $orders);
        return (new OrderListResponse())->setOrderList($orders);
    }

    /**
     * @Rest\Get(path="/order_status_history/")
     * @see OrderStatusHistoryRequest
     * @see OrderStatusHistoryResponse
     */
    public function getOrderStatusHistoryAction()
    {
    }

    /**
     * @Rest\Get(path="/order_info/")
     * @see OrderInfoRequest
     * @see OrderInfoResponse
     */
    public function getOrderInfoAction()
    {
    }
}
