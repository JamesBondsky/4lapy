<?php

/**
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Dto\Request\OrderInfoRequest;
use FourPaws\MobileApiBundle\Dto\Request\OrderStatusHistoryRequest;
use FourPaws\MobileApiBundle\Dto\Request\PaginationRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\OrderInfoResponse;
use FourPaws\MobileApiBundle\Dto\Response\OrderListResponse;
use FourPaws\MobileApiBundle\Dto\Response\OrderStatusHistoryResponse;
use FourPaws\PersonalBundle\Repository\OrderRepository;
use FourPaws\SaleBundle\Exception\OrderCancelException;
use FourPaws\SaleBundle\Exception\OrderExtendException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FourPaws\MobileApiBundle\Services\Api\OrderService as ApiOrderService;
use FourPaws\SaleBundle\Service\OrderService as AppOrderService;
use FourPaws\SaleBundle\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PushController
 * @package FourPaws\MobileApiBundle\Controller
 * @Security("has_role('REGISTERED_USERS')")
 */
class OrderController extends BaseController
{
    /**
     * @var ApiOrderService
     */
    private $apiOrderService;

    /**
     * @var AppOrderService
     */
    private $appOrderService;

    public function __construct(
        ApiOrderService $apiOrderService,
        AppOrderService $appOrderService
    )
    {
        $this->apiOrderService = $apiOrderService;
        $this->appOrderService = $appOrderService;
    }
    /**
     * @Rest\Get(path="/order_list_v2/")
     * @Rest\View()
     * @param PaginationRequest $paginationRequest
     * @param OrderRepository $orderRepository
     * @return OrderListResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    public function getOrderListAction(PaginationRequest $paginationRequest, OrderRepository $orderRepository)
    {
        $orders = $this->apiOrderService->getList($paginationRequest)->getValues();
        $pagination = $orderRepository->getNav();

        return (new OrderListResponse())
            ->setOrderList($orders)
            ->setTotalItems($pagination->getRecordCount())
            ->setTotalPages($pagination->getPageCount())
        ;
    }

    /**
     * @Rest\Get(path="/order_status_history/")
     * @Rest\View()
     * @param OrderStatusHistoryRequest $orderStatusHistoryRequest
     * @return OrderStatusHistoryResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getOrderStatusHistoryAction(OrderStatusHistoryRequest $orderStatusHistoryRequest)
    {
        $orderNumber = $orderStatusHistoryRequest->getOrderNumber();
        $statusHistory = $this->apiOrderService->getHistoryForCurrentUser($orderNumber);
        return new OrderStatusHistoryResponse($statusHistory);
    }

    /**
     * @Rest\Get(path="/order_info/")
     * @Rest\View()
     * @param OrderInfoRequest $orderInfoRequest
     * @return OrderInfoResponse
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\EmptyEntityClass
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getOrderInfoAction(OrderInfoRequest $orderInfoRequest)
    {
        $orderNumber = $orderInfoRequest->getOrderNumber();
        $order = $this->apiOrderService->getOneByNumberForCurrentUser($orderNumber);
        return new OrderInfoResponse($order);
    }

    /**
     * @Rest\Patch(path="/order_cancel/")
     * @Rest\View()
     *
     * @param Request $request
     * @return Response
     */
    public function patchOrderCancelAction(Request $request): Response
    {
        $response = new Response();
        $cancelResult = false;

        $error = false;

        try {
            $orderId = $request->get('orderId');
            $cancelResult = $this->appOrderService->cancelOrder($orderId);
        } catch (OrderCancelException | NotFoundException  $e) {
            $error = $e->getMessage();
        } catch (\Exception $e) {
            $error = 'При отмене заказа произошла ошибка';
        }

        if (!$cancelResult) {
            $error = 'При отмене заказа произошла ошибка';
        }

        if ($error) {
            $response->setData(['success' => false]);
            $response->addError(new Error(0, $error));
        } else {
            $response->setData(['success' => true]);
        }

        return $response;
    }

    /**
     * @Rest\Patch(path="/order_extend/")
     * @Rest\View()
     *
     * @param Request $request
     * @return Response
     */
    public function patchOrderExtendAction(Request $request): Response
    {
        $response = new Response();
        $extendResult = false;

        $error = false;

        try {
            $orderId = $request->get('orderId');
            $extendResult = $this->appOrderService->extendOrder($orderId);
        } catch (OrderExtendException | NotFoundException  $e) {
            $error = $e->getMessage();
        } catch (\Exception $e) {
            $error = 'При продлении срока хранения произошла ошибка';
        }

        if (!$extendResult) {
            $error = 'При продлении срока хранения произошла ошибка';
        }

        if ($error) {
            $response->setData(['success' => false]);
            $response->addError(new Error(0, $error));
        } else {
            $response->setData(['success' => true]);
        }

        return $response;
    }
}
