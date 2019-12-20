<?php

/**
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Request\OrderInfoRequest;
use FourPaws\MobileApiBundle\Dto\Request\OrderStatusHistoryRequest;
use FourPaws\MobileApiBundle\Dto\Request\PaginationRequest;
use FourPaws\MobileApiBundle\Dto\Response\OrderInfoResponse;
use FourPaws\MobileApiBundle\Dto\Response\OrderListResponse;
use FourPaws\MobileApiBundle\Dto\Response\OrderStatusHistoryResponse;
use FourPaws\MobileApiBundle\Exception\OrderNotFoundException;
use FourPaws\PersonalBundle\Repository\OrderRepository;
use FourPaws\SaleBundle\Exception\OrderCancelException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FourPaws\MobileApiBundle\Services\Api\OrderService as ApiOrderService;
use Symfony\Component\HttpFoundation\Request;
use FourPaws\App\Application;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\MobileApiBundle\Dto\Response;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Services\Api\OrderService as MobileOrderService;
use FourPaws\UserBundle\Service\UserService as AppUserService;
use FourPaws\PersonalBundle\Service\OrderService as PersonalOrderService;

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
    
    public function __construct(
        ApiOrderService $apiOrderService
    ) {
        $this->apiOrderService = $apiOrderService;
    }
    
    /**
     * @Rest\Get(path="/order_list_v2/")
     * @Rest\View()
     * @param PaginationRequest $paginationRequest
     * @param OrderRepository   $orderRepository
     * @return OrderListResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    public function getOrderListAction(PaginationRequest $paginationRequest, OrderRepository $orderRepository)
    {
        $orders     = $this->apiOrderService->getList($paginationRequest)->getValues();
        $pagination = $orderRepository->getNav();
        
        return (new OrderListResponse())
            ->setOrderList($orders)
            ->setTotalItems($pagination->getRecordCount())
            ->setTotalPages($pagination->getPageCount());
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
        $orderNumber   = $orderStatusHistoryRequest->getOrderNumber();
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
        $order       = $this->apiOrderService->getOneByNumberForCurrentUser($orderNumber);
        return new OrderInfoResponse($order);
    }
    
    /**
     * @Rest\Patch(path="/order_cancel/")
     * @Rest\View()
     *
     * @param Request $request
     *
     * @return Response
     * @throws ApplicationCreateException
     */
    public function orderCancelAction(Request $request)
    {
        try {
            $serviceContainer     = Application::getInstance()->getContainer();
            $orderService         = $serviceContainer->get(OrderService::class);
            $mobileOrderService   = $serviceContainer->get(MobileOrderService::class);
            $number = $request->get('orderId');
            
            $orderId = $mobileOrderService->getOrderIdByNumber($number);
            $cancelResult = $orderService->cancelOrder($orderId);
        } catch (OrderCancelException | \FourPaws\SaleBundle\Exception\NotFoundException  $e) {
            $errors = new ArrayCollection([new Error(0, $e->getMessage())]);
            
            return (new Response())->setData([
                'success' => 0,
            ])->setErrors($errors);
        } catch (\Exception $e) {
            $errors = new ArrayCollection([new Error(0, $e->getMessage())]);
            
            return (new Response())->setData([
                'success' => 0,
            ])->setErrors($errors);
        }
        
        if (!$cancelResult) {
            $errors = new ArrayCollection([new Error(0, 'При отмене заказа произошла ошибка. Повторите запрос позже.')]);
            
            return (new Response())->setData([
                'success' => 0,
            ])->setErrors($errors);
        }
        
        if ($cancelResult == 'canceling') {
            $errors = new ArrayCollection([new Error(0, 'Ваш заказ уже передан в службу доставки. Мы передадим информацию об отмене заказа.')]);
        
            return (new Response([
                'order' => $mobileOrderService->getOneByNumberForCurrentUser($number),
            ]))->setErrors($errors);
        }
    
        //APPTEKA захотела читать текст попапа из errors)
        $errors = new ArrayCollection([new Error(0, 'Ваш закакз отменён!')]);
        
        return (new Response([
            'order' => $mobileOrderService->getOneByNumberForCurrentUser($number),
        ]))->setErrors($errors);
    }
}
