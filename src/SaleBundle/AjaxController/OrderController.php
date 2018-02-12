<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\AjaxController;

use Bitrix\Main\Web\Uri;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\ReCaptcha\ReCaptchaService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Exception\OrderStorageValidationException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;

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

    /**
     * @var OrderStorageService
     */
    private $orderStorageService;

    /**
     * @var UserAuthorizationInterface
     */
    private $userAuthProvider;

    /**
     * @var ReCaptchaService
     */
    private $recaptcha;

    protected $stepOrder = [
        OrderStorageService::AUTH_STEP,
        OrderStorageService::DELIVERY_STEP,
        OrderStorageService::PAYMENT_STEP,
        OrderStorageService::COMPLETE_STEP,
    ];

    /**
     * OrderController constructor.
     *
     * @param OrderService $orderService
     * @param OrderStorageService $orderStorageService
     * @param UserAuthorizationInterface $userAuthProvider
     * @param ReCaptchaService $recaptcha
     */
    public function __construct(
        OrderService $orderService,
        OrderStorageService $orderStorageService,
        UserAuthorizationInterface $userAuthProvider,
        ReCaptchaService $recaptcha
    ) {
        $this->orderService = $orderService;
        $this->orderStorageService = $orderStorageService;
        $this->userAuthProvider = $userAuthProvider;
        $this->recaptcha = $recaptcha;
    }

    /**
     * @Route("/store-search/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function storeSearchAction(Request $request): JsonResponse
    {
        \CBitrixComponent::includeComponentClass('fourpaws:order.shop.list');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $shopListClass = new \FourPawsOrderShopListComponent();

        return JsonSuccessResponse::createWithData(
            'Подгрузка успешна',
            $shopListClass->getStores(
                $shopListClass->getFilterByRequest($request),
                $shopListClass->getOrderByRequest($request)
            )
        );
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
        $storage = $this->orderStorageService->getStorage();
        if (!$this->userAuthProvider->isAuthorized() && !$storage->isCaptchaFilled()) {
            $request->request->add(['captchaFilled' => $this->recaptcha->checkCaptcha()]);
        }
        $validationErrors = $this->fillStorage($storage, $request, OrderStorageService::AUTH_STEP);

        if (!empty($validationErrors)) {
            return JsonErrorResponse::createWithData(
                '',
                ['errors' => $validationErrors],
                200,
                ['reload' => true]
            );
        }

        return JsonSuccessResponse::create(
            '',
            200,
            [],
            ['redirect' => '/sale/order/' . OrderStorageService::DELIVERY_STEP . '/']
        );
    }

    /**
     * @Route("/validate/delivery", methods={"POST"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return JsonResponse
     */
    public function validateDeliveryAction(Request $request): JsonResponse
    {
        $storage = $this->orderStorageService->getStorage();
        $currentStep = OrderStorageService::DELIVERY_STEP;
        if ($this->orderStorageService->validateStorage($storage, $currentStep) !== $currentStep) {
            return JsonErrorResponse::create('', 200, [], ['reload' => true]);
        }

        $validationErrors = $this->fillStorage($storage, $request, $currentStep);
        if (!empty($validationErrors)) {
            return JsonErrorResponse::createWithData('', ['errors' => $validationErrors]);
        }

        return JsonSuccessResponse::create(
            '',
            200,
            [],
            ['redirect' => '/sale/order/' . OrderStorageService::PAYMENT_STEP . '/']
        );
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
        $storage = $this->orderStorageService->getStorage();
        $currentStep = OrderStorageService::PAYMENT_STEP;
        if ($this->orderStorageService->validateStorage($storage, $currentStep) !== $currentStep) {
            return JsonErrorResponse::create('', 200, [], ['reload' => true]);
        }

        $validationErrors = $this->fillStorage($storage, $request, $currentStep);
        if (!empty($validationErrors)) {
            return JsonErrorResponse::createWithData('', ['errors' => $validationErrors]);
        }

        try {
            $order = $this->orderService->createOrder($storage);
        } catch (OrderCreateException $e) {
            return JsonErrorResponse::createWithData('', ['errors' => ['order' => 'Ошибка при создании заказа']]);
        }

        $url = new Uri('/sale/order/' . OrderStorageService::COMPLETE_STEP . '/' . $order->getId());
        if (!$this->userAuthProvider->isAuthorized()) {
            $url->addParams(['hash' => $order->getHash()]);
        }

        return JsonSuccessResponse::create(
            '',
            200,
            [],
            ['redirect' => $url->getUri()]
        );
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

    protected function fillStorage(OrderStorage $storage, Request $request, string $step): array
    {
        $errors = [];
        $this->orderStorageService->setStorageValuesFromRequest(
            $storage,
            $request,
            $step
        );

        try {
            $this->orderStorageService->updateStorage($storage, $step);
        } catch (OrderStorageValidationException $e) {
            /** @var ConstraintViolation $error */
            foreach ($e->getErrors() as $i => $error) {
                $key = $error->getPropertyPath() ?: $i;
                $errors[$key] = $error->getMessage();
            }
        }

        return $errors;
    }
}
