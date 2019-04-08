<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\Payment;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\PersonalBundle\Exception\OrderSubscribeException;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\ReCaptchaBundle\Service\ReCaptchaService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Enum\OrderStorage as OrderStorageEnum;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Exception\OrderSplitException;
use FourPaws\SaleBundle\Exception\OrderStorageSaveException;
use FourPaws\SaleBundle\Exception\OrderStorageValidationException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\SaleBundle\Service\ShopInfoService;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Protobuf\Exception;
use Psr\Log\LoggerAwareInterface;
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
class OrderController extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;
    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var OrderStorageService
     */
    private $orderStorageService;

    /**
     * @var OrderSubscribeService
     */
    private $orderSubscribeService;

    /**
     * @var UserAuthorizationInterface
     */
    private $userAuthProvider;

    /**
     * @var ShopInfoService
     */
    private $shopInfoService;

    /**
     * @var DeliveryService
     */
    private $deliveryService;

    /**
     * @var ReCaptchaService
     */
    private $recaptcha;

    /**
     * OrderController constructor.
     *
     * @param OrderService               $orderService
     * @param DeliveryService            $deliveryService
     * @param OrderStorageService        $orderStorageService
     * @param OrderSubscribeService      $orderSubscribeService
     * @param UserAuthorizationInterface $userAuthProvider
     * @param ShopInfoService            $shopInfoService
     * @param ReCaptchaService           $recaptcha
     */
    public function __construct(
        OrderService $orderService,
        DeliveryService $deliveryService,
        OrderStorageService $orderStorageService,
        OrderSubscribeService $orderSubscribeService,
        UserAuthorizationInterface $userAuthProvider,
        ShopInfoService $shopInfoService,
        ReCaptchaService $recaptcha
    )
    {
        $this->orderService = $orderService;
        $this->deliveryService = $deliveryService;
        $this->orderStorageService = $orderStorageService;
        $this->orderSubscribeService = $orderSubscribeService;
        $this->userAuthProvider = $userAuthProvider;
        $this->shopInfoService = $shopInfoService;
        $this->recaptcha = $recaptcha;
    }

    /**
     * @Route("/store-search/", methods={"GET"})
     *
     * @throws SystemException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @return JsonResponse
     */
    public function storeSearchAction(): JsonResponse
    {
        $storage = $this->orderStorageService->getStorage();

        return JsonSuccessResponse::createWithData(
            'Подгрузка успешна',
            $this->shopInfoService->toArray(
                $this->shopInfoService->getShopInfo(
                    $storage,
                    $this->orderStorageService->getPickupDelivery($storage)
                )
            )
        );
    }

    /**
     * @Route("/store-info/", methods={"GET"})
     *
     * @param Request $request
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws OrderStorageSaveException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws UserMessageException
     * @throws \Exception
     * @return JsonResponse
     */
    public function storeInfoAction(Request $request): JsonResponse
    {
        $storage = $this->orderStorageService->getStorage();
        $selectedStore = $request->get('shop', '');

        return JsonSuccessResponse::createWithData(
            'Подгрузка успешна',
            $this->shopInfoService->toArray(
                $this->shopInfoService->getOneShopInfo(
                    $selectedStore,
                    $storage,
                    $this->orderStorageService->getPickupDelivery($storage)
                )
            )
        );
    }

    /**
     * @Route("/delivery-intervals/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ArgumentException
     * @throws OrderStorageSaveException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws UserMessageException
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     */
    public function deliveryIntervalsAction(Request $request): JsonResponse
    {
        $result = [];
        $date = (int)$request->get('deliveryDate', 0);
        $deliveries = $this->orderStorageService->getDeliveries($this->orderStorageService->getStorage());
        $delivery = null;
        foreach ($deliveries as $deliveryItem) {
            if (!$this->deliveryService->isDelivery($deliveryItem)) {
                continue;
            }

            $delivery = $deliveryItem;
        }

        if (null === $delivery) {
            return JsonSuccessResponse::createWithData(
                '',
                $result
            );
        }

        /** @var DeliveryResultInterface $delivery */
        if ($delivery = $this->deliveryService->getNextDeliveries($delivery, 10)[$date]) {
            $intervals = $delivery->getAvailableIntervals();

            /** @var Interval $interval */
            foreach ($intervals as $i => $interval) {

                /** Для зон 2 и 5 выключаем 31.12.2018 доставки после 18:00 */
                if ((true)
                    && ($delivery->getDeliveryDate()->format('d.m.Y') == '31.12.2018')
                    && (in_array($delivery->getDeliveryZone(), ['ZONE_2', 'ZONE_5']))
                    && (($interval->getTo() > 18) || ($interval->getTo() == 0))
                ) {
                    continue;
                }

                $result[] = [
                    'name'  => (string)$interval,
                    'value' => $i + 1,
                ];
            }
        }

        return JsonSuccessResponse::createWithData(
            '',
            $result
        );
    }

    /**
     * @Route("/validate/bonus-card", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ArgumentException
     * @throws OrderStorageSaveException
     * @throws SystemException
     * @throws ObjectPropertyException
     */
    public function validateBonusCardAction(Request $request): JsonResponse
    {
        $storage = $this->orderStorageService->getStorage();
        [$validationErrors] = $this->fillStorage($storage, $request, OrderStorageEnum::PAYMENT_STEP_CARD);

        if (!empty($validationErrors)) {
            return JsonErrorResponse::createWithData(
                '',
                ['errors' => $validationErrors],
                200,
                ['reload' => false]
            );
        }

        return JsonSuccessResponse::create(
            '',
            200,
            []
        );
    }

    /**
     * @Route("/validate/auth", methods={"POST"})
     * @param Request $request
     *
     * @throws SystemException
     * @return JsonResponse
     * @return JsonResponse
     * @throws OrderStorageSaveException
     */
    public function validateAuthAction(Request $request): JsonResponse
    {
        $currentStep = OrderStorageEnum::AUTH_STEP;
        $storage = $this->orderStorageService->getStorage();
        if (!$this->userAuthProvider->isAuthorized() && !$storage->isCaptchaFilled()) {
            $request->request->add(['captchaFilled' => $this->recaptcha->checkCaptcha()]);
        }
        [$validationErrors] = $this->fillStorage($storage, $request, $currentStep);

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
            ['redirect' => '/sale/order/' . $this->getNextStep($currentStep) . '/']
        );
    }

    /**
     * @Route("/validate/delivery", methods={"POST"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws OrderStorageSaveException
     * @throws SystemException
     */
    public function validateDeliveryAction(Request $request): JsonResponse
    {
        $currentStep = OrderStorageEnum::DELIVERY_STEP;
        [
            $validationErrors,
            $realStep,
        ] = $this->fillStorage(
            $this->orderStorageService->getStorage(),
            $request,
            $currentStep
        );
        if (!empty($validationErrors)) {
            return JsonErrorResponse::createWithData(
                '',
                ['errors' => $validationErrors],
                200,
                ['reload' => isset($validationErrors[OrderStorageService::SESSION_EXPIRED_VIOLATION]) || ($realStep !== $currentStep)]
            );
        }

        return JsonSuccessResponse::create(
            '',
            200,
            [],
            ['redirect' => '/sale/order/' . $this->getNextStep($currentStep) . '/']
        );
    }

    /**
     * @Route("/validate/payment", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws BitrixProxyException
     * @throws LoaderException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderStorageSaveException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws UserMessageException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function validatePaymentAction(Request $request): JsonResponse
    {
        $currentStep = OrderStorageEnum::PAYMENT_STEP;
        $storage = $this->orderStorageService->getStorage();
        [
            $validationErrors,
            $realStep,
        ] = $this->fillStorage(
            $storage,
            $request,
            $currentStep
        );
        if (!empty($validationErrors)) {
            return JsonErrorResponse::createWithData(
                '',
                ['errors' => $validationErrors],
                200,
                ['reload' => isset($validationErrors[OrderStorageService::SESSION_EXPIRED_VIOLATION]) || ($realStep !== $currentStep)]
            );
        }

        try {
            $order = $this->orderService->createOrder($storage);
        } catch (OrderCreateException|OrderSplitException $e) {
            $this->log()->error(sprintf('failed to create order: %s', $e->getMessage()), [
                'storage' => $this->orderStorageService->storageToArray($storage),
            ]);

            return JsonErrorResponse::createWithData('', ['errors' => ['order' => 'Ошибка при создании заказа']]);
        }

        $url = new Uri('/sale/order/' . $this->getNextStep($currentStep) . '/' . $order->getId() . '/');

        /** @var Payment $payment */
        foreach ($order->getPaymentCollection() as $payment) {
            if ($payment->isInner()) {
                continue;
            }
            if ($payment->getPaySystem()->getField('CODE') === OrderPayment::PAYMENT_ONLINE) {
                $url->setPath('/sale/payment/');
                $url->addParams(['ORDER_ID' => $order->getId()]);
                if (!$this->orderService->hasRelatedOrder($order)) {
                    $url->addParams(['PAY' => 'Y']);
                }
            }
        }

        $url->addParams(['HASH' => $order->getHash()]);

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
     * @return string|null
     */
    protected function getNextStep(string $step): ?string
    {
        $key = array_search($step, OrderStorageEnum::STEP_ORDER, true);

        return OrderStorageEnum::STEP_ORDER[++$key];
    }

    /**
     * @param OrderStorage $storage
     * @param Request      $request
     * @param string       $step
     *
     * @return array
     * @throws ArgumentException
     * @throws SystemException
     * @throws ObjectPropertyException
     */
    protected function fillStorage(OrderStorage $storage, Request $request, string $step): array
    {
        $errors = [];

        try{
            $this->orderStorageService->setStorageValuesFromRequest(
                $storage,
                $request,
                $step
            );
        } catch (\Exception $e){
            $errors[] = $e->getMessage();
        }

        if(empty($errors)){
            try {
                $this->orderStorageService->updateStorage($storage, $step);

                if($storage->isSubscribe()){
                    if($step == OrderStorageEnum::DELIVERY_STEP){ // создание подписки на доставку
                        $result = $this->orderSubscribeService->createSubscriptionByRequest($storage, $request);
                        if(!$result->isSuccess()){
                            $this->log()->error(implode(";\r\n", $result->getErrorMessages()));
                            throw new OrderSubscribeException("Произошла ошибка оформления подписки на доставку, пожалуйста, обратитесь к администратору");
                        }
                        $storage->setSubscribeId($result->getData()['subscribeId']);
                        $this->orderStorageService->updateStorage($storage, $step);
                    } else if($step == OrderStorageEnum::PAYMENT_STEP){ // установка свойства "Списывать все баллы по подписке"
                        if($storage->isSubscribe() && $request->get('subscribeBonus')){
                            $subscribe = $this->orderSubscribeService->getById($storage->getSubscribeId());
                            $subscribe->setPayWithbonus(true);
                            $this->orderSubscribeService->update($subscribe);
                        }
                    }
                }
            } catch (OrderStorageValidationException $e) {
                /** @var ConstraintViolation $error */
                foreach ($e->getErrors() as $i => $error) {
                    $key = $error->getPropertyPath() ?: $error->getCode() ?: $i;
                    $errors[$key] = $error->getMessage();
                }
                $step = $e->getRealStep();
            } catch (\Exception $e) {
                $errors[$e->getCode()] = "Произошла ошибка, пожалуйста, обратитесь к администратору";
                $this->log()->error(sprintf('Error in order creating: %s: %s', \get_class($e), $e->getMessage()));
            }
        }

        return [
            $errors,
            $step,
        ];
    }
}
