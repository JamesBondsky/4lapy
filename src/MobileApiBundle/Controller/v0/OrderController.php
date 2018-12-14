<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Object\OrderStatus;
use FourPaws\MobileApiBundle\Dto\Request\OrderInfoRequest;
use FourPaws\MobileApiBundle\Dto\Request\OrderStatusHistoryRequest;
use FourPaws\MobileApiBundle\Dto\Response\OrderInfoResponse;
use FourPaws\MobileApiBundle\Dto\Response\OrderListResponse;
use FourPaws\MobileApiBundle\Dto\Response\OrderStatusHistoryResponse;
use FourPaws\PersonalBundle\Entity\Order;
use \FourPaws\MobileApiBundle\Dto\Object\Order as ApiObjectOrder;
use FourPaws\PersonalBundle\Service\OrderService;
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
     */
    public function getOrderListAction(OrderService $orderService)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $orders = $orderService->getUserOrders($user)->getValues();
        $orders = array_map(function (Order $order) {

            $dateInsert = (new \DateTime())->setTimestamp($order->getDateInsert()->getTimestamp());

            $status = (new OrderStatus())
                ->setTitle($order->getStatus())
                ->setCode($order->getStatusId());

            return (new ApiObjectOrder())
                ->setId($order->getId())
                ->setDateFormat($dateInsert)
                // ->setReviewEnabled($order->) // toDo reviews выбираются из таблички opros_checks, поля opros_4, opros_5, opros_8
                ->setStatus($status)
                ->setCompleted($order->isClosed())
                ->setPaid($order->isPayed());
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
