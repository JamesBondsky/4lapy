<?php

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\OrderInfoRequest;
use FourPaws\MobileApiBundle\Dto\Request\OrderStatusHistoryRequest;
use FourPaws\MobileApiBundle\Dto\Response\OrderInfoResponse;
use FourPaws\MobileApiBundle\Dto\Response\OrderListResponse;
use FourPaws\MobileApiBundle\Dto\Response\OrderStatusHistoryResponse;

class OrderController extends FOSRestController
{
    /**
     * @Rest\Get(path="/order_list")
     * @see OrderListResponse
     */
    public function getOrderListAction()
    {
    }

    /**
     * @Rest\Get(path="/order_status_history")
     * @see OrderStatusHistoryRequest
     * @see OrderStatusHistoryResponse
     */
    public function getOrderStatusHistoryAction()
    {
    }

    /**
     * @Rest\Get(path="/order_info")
     * @see OrderInfoRequest
     * @see OrderInfoResponse
     */
    public function getOrderInfoAction()
    {
    }
}
