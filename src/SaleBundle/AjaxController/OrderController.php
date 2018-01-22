<?php

namespace FourPaws\SaleBundle\AjaxController;

use FourPaws\App\Response\JsonResponse;
use FourPaws\SaleBundle\Exception\OrderStorageValidationException;
use FourPaws\SaleBundle\Service\OrderService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BasketController
 *
 * @package FourPaws\SaleBundle\Controller
 * @Route("/order")
 */
class OrderController extends Controller
{
    /**
     * @var OrderService
     */
    private $orderService;

    protected $stepOrder = [
        OrderService::AUTH_STEP,
        OrderService::DELIVERY_STEP,
        OrderService::PAYMENT_STEP,
        OrderService::COMPLETE_STEP,
    ];

    /**
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @Route("/validate/auth", methods={"POST"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function validateAuthAction(Request $request): JsonResponse
    {
        $storage = $this->orderService->setStorageValuesFromRequest(
            $this->orderService->getStorage(),
            $request
        );
        try {
            $this->orderService->updateStorage($storage, OrderService::AUTH_STEP);
        } catch (OrderStorageValidationException $e) {
        }
    }

    /**
     * @Route("/validate/delivery", methods={"POST"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function validateDeliveryAction(Request $request): JsonResponse
    {
        $storage = $this->orderService->setStorageValuesFromRequest(
            $this->orderService->getStorage(),
            $request
        );
        try {
            $this->orderService->updateStorage($storage, OrderService::DELIVERY_STEP);
        } catch (OrderStorageValidationException $e) {
        }
    }

    /**
     * @Route("/validate/payment", methods={"POST"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \FourPaws\App\Response\JsonResponse
     */
    public function validatePaymentAction(Request $request): JsonResponse
    {
        $storage = $this->orderService->setStorageValuesFromRequest(
            $this->orderService->getStorage(),
            $request
        );
        try {
            $this->orderService->updateStorage($storage, OrderService::PAYMENT_STEP);
        } catch (OrderStorageValidationException $e) {
        }
    }

    /**
     * @param string $step
     *
     * @return mixed
     */
    protected function getNextStep(string $step)
    {
        $key = array_search($step, $this->stepOrder, true);

        return $this->stepOrder[++$key];
    }
}
