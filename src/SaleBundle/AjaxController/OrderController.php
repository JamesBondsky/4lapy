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
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\KioskBundle\Service\KioskService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Exception\OrderSubscribeException;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\ReCaptchaBundle\Service\ReCaptchaService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Enum\OrderStorage as OrderStorageEnum;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\OrderCancelException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Exception\OrderExtendException;
use FourPaws\SaleBundle\Exception\OrderSplitException;
use FourPaws\SaleBundle\Exception\OrderStorageSaveException;
use FourPaws\SaleBundle\Exception\OrderStorageValidationException;
use FourPaws\SaleBundle\Repository\Table\AnimalShelterTable;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\SaleBundle\Service\ShopInfoService;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\ShopInfoService as StoreShopInfoService;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Psr\Log\LoggerAwareInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use FourPaws\App\Application as App;

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
     * @var StoreShopInfoService
     */
    private $storeShopInfoService;

    /**
     * @var LocationService
     */
    private $locationService;

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
     * @param OrderService $orderService
     * @param DeliveryService $deliveryService
     * @param OrderStorageService $orderStorageService
     * @param OrderSubscribeService $orderSubscribeService
     * @param UserAuthorizationInterface $userAuthProvider
     * @param ShopInfoService $shopInfoService
     * @param StoreShopInfoService $storeShopInfoService
     * @param LocationService $locationService
     * @param ReCaptchaService $recaptcha
     */
    public function __construct(
        OrderService $orderService,
        DeliveryService $deliveryService,
        OrderStorageService $orderStorageService,
        OrderSubscribeService $orderSubscribeService,
        UserAuthorizationInterface $userAuthProvider,
        ShopInfoService $shopInfoService,
        StoreShopInfoService $storeShopInfoService,
        LocationService $locationService,
        ReCaptchaService $recaptcha
    )
    {
        $this->orderService = $orderService;
        $this->deliveryService = $deliveryService;
        $this->orderStorageService = $orderStorageService;
        $this->orderSubscribeService = $orderSubscribeService;
        $this->userAuthProvider = $userAuthProvider;
        $this->shopInfoService = $shopInfoService;
        $this->storeShopInfoService = $storeShopInfoService;
        $this->locationService = $locationService;
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

        $shopInfo = $this->shopInfoService->toArray(
            $this->shopInfoService->getShopInfo(
                $storage,
                $this->orderStorageService->getPickupDelivery($storage)
            )
        );
        array_walk($shopInfo['items'], [$this->storeShopInfoService, 'locationTypeSortDecorate']);
        usort($shopInfo['items'], [$this->storeShopInfoService, 'shopCompareByLocationType']);
        if (KioskService::isKioskMode()){
            usort($shopInfo['items'], [$this->storeShopInfoService, 'shopCompareByKiosk']);
        }
        array_walk($shopInfo['items'], [$this->storeShopInfoService, 'locationTypeSortUndecorate']);

        return JsonSuccessResponse::createWithData(
            'Подгрузка успешна',
            $shopInfo
        );
    }

    /**
     * @Route("/shelter-search/", methods={"GET"})
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function shelterSearchAction(): JsonResponse
    {
        $shelters = AnimalShelterTable::getList()->fetchAll();
        return JsonSuccessResponse::createWithData(
            'Подгрузка успешна',
            $shelters
        );
    }

    /**
     * @Route("/store-search-by-items/", methods={"POST"})
     *
     * @throws SystemException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @return JsonResponse
     */
    public function storeSearchByItemsAction(Request $request): JsonResponse
    {
        /** @var BasketService $basketService */
        $basketService = App::getInstance()->getContainer()->get(BasketService::class);
        /** @var DeliveryService $deliveryService */
        $deliveryService = App::getInstance()->getContainer()->get('delivery.service');
        $storage = $this->orderStorageService->getStorage();
        $items = $request->get('items');

        if(empty($items)){
            return JsonSuccessResponse::create('Не переданы товары для создания службы доставки');
        }

        // данные с 2 шага оформления заказа помешают расчётам
        if($storage->getDeliveryId() > 0){
            $this->orderStorageService->clearStorage($storage);
            $storage = $this->orderStorageService->getStorage();
        }

        // получение самовывоза из набора товаров
        $basket = $basketService->createBasketFromItems($items);
        $deliveries = $deliveryService->getByBasket($basket, '', [DeliveryService::INNER_PICKUP_CODE]);
        if(empty($deliveries)){
            return JsonSuccessResponse::create('Нет доступных магазинов для самовывоза');
        }
        $pickup = current($deliveries);

        return JsonSuccessResponse::createWithData(
            'Подгрузка успешна',
            $this->shopInfoService->toArray(
                $this->shopInfoService->getShopInfo(
                    $storage,
                    $pickup
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
     * @throws OrderStorageValidationException
     * @throws SystemException
     */
    public function deliveryIntervalsAction(Request $request): JsonResponse
    {
        $result = [];
        $date = (int)$request->get('deliveryDate', 0);
        $currentStep = OrderStorageEnum::NOVALIDATE_STEP;
        $okato = (int)$request->get('okato', 0);
        $storage = $this->orderStorageService->getStorage();

        if ($okato > 0) {
            /**
             * Ищем зону по ОКАТО
             */
            $okato = substr($okato, 0, 8);
            $locations = $this->locationService->findLocationByExtService(LocationService::OKATO_SERVICE_CODE, $okato);
            if (count($locations)) {
                /**
                 * Обновляем storage, записываем зону
                 */
                $location = current($locations);
                $defaultCity = $storage->getCity();
                $defaultCityCode = $storage->getCityCode();
                $storage->setCity($location['NAME']);
                $storage->setCityCode($location['CODE']);
                $storage->setMoscowDistrictCode($location['CODE']);
                $this->orderStorageService->updateStorage($storage, $currentStep);

                /**
                 * Получаем цену доставки в этой зоне
                 */
                $innerDelivery = $this->orderStorageService->getInnerDelivery($storage);

                if (!$innerDelivery) {
                    $storage->setCity($defaultCity);
                    $storage->setCityCode($defaultCityCode);
                    $this->orderStorageService->updateStorage($storage, $currentStep);
                }
            }
        }

        $deliveries = $this->orderStorageService->getDeliveries($storage);
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

        /**
         * Moscow Districts
         */
        if ($storage->getMoscowDistrictCode() != '') {
            $this->orderStorageService->updateStorageMoscowZone($storage, OrderStorageEnum::NOVALIDATE_STEP);

            // необходимо обновить службы доставки, чтобы применилась зона
            $this->orderStorageService->getDeliveries($storage, true);
        }


        try {
            $order = $this->orderService->createOrder($storage);
        } catch (OrderCreateException|OrderSplitException $e) {
            $this->log()->error(sprintf('failed to create order: %s', $e->getMessage()), [
                'storage' => $this->orderStorageService->storageToArray($storage),
            ]);

            return JsonErrorResponse::createWithData('', ['errors' => ['order' => 'Ошибка при создании заказа']]);
        } catch (BitrixRuntimeException $e) {
            if (strpos($e->getMessage(), 'Пользователь с таким e-mail') !== false) {
                return JsonErrorResponse::createWithData('', ['errors' => ['order' => 'Пользователь с таким e-mail уже существует']]);
            } else {
                $this->log()->error(__METHOD__ . '. Не удалось создать заказ. ', $e->getMessage());
                throw new BitrixRuntimeException($e->getMessage(), $e->getCode());
            }
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

        if (empty($errors)) {
            // проверка на корректность даты доставки
            try {
                /*
                 * Обнуление просиходит в fourpaws:order для того, что бы был редирект на нужный, тут просто валидация
                 */
                if (($step !== OrderStorageEnum::AUTH_STEP) && (!$this->orderStorageService->validateDeliveryDate($storage))) {
                    $step = OrderStorageEnum::AUTH_STEP;
                    $errors[] = 'Некорректная дата доставки!';
                }
            } catch (\Exception $e) {
            }
        }

        if (empty($errors)) {
            try {
                $this->orderStorageService->updateStorage($storage, $step);

                // создание подписки на доставку и установка свойства "Списывать все баллы по подписке"
                if ($storage->isSubscribe()) {
                    if ($step === OrderStorageEnum::DELIVERY_STEP) {
                        $result = $this->orderSubscribeService->createSubscriptionByRequest($storage, $request);
                        if (!$result->isSuccess()) {
                            $this->log()->error(implode(";\r\n", $result->getErrorMessages()));
                            throw new OrderSubscribeException('Произошла ошибка оформления подписки на доставку, пожалуйста, обратитесь к администратору');
                        }
                        $storage->setSubscribeId($result->getData()['subscribeId']);
                        $this->orderStorageService->updateStorage($storage, $step);
                    } else if (($step === OrderStorageEnum::PAYMENT_STEP) && $request->get('subscribeBonus')) {
                        $subscribe = $this->orderSubscribeService->getById($storage->getSubscribeId());
                        if ($subscribe) {
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
                $errors[$e->getCode()] = 'Произошла ошибка, пожалуйста, обратитесь к администратору';
                $this->log()->error(sprintf('Error in order creating: %s: %s', \get_class($e), $e->getMessage()));
            }
        }

        return [
            $errors,
            $step,
        ];
    }

    /**
     * @Route("/set_delivery_zone_by_address/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderStorageSaveException
     * @throws OrderStorageValidationException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws UserMessageException
     */
    public function setDeliveryZoneByAddressAction(Request $request): JsonResponse
    {
        $storage = $this->orderStorageService->getStorage();
        $currentStep = OrderStorageEnum::NOVALIDATE_STEP;
        $data = json_decode($request->get('data'), true);
        $okato = $data['data']['okato'];
        if (!$okato) {
            $storage->setMoscowDistrictCode('');
            $storage->setCity(DeliveryService::MOSCOW_LOCATION_NAME);
            $storage->setCityCode(DeliveryService::MOSCOW_LOCATION_CODE);
            $this->orderStorageService->updateStorage($storage, $currentStep);
        } else {
            /**
             * Ищем зону по ОКАТО
             */
            $okato = substr($okato, 0, 8);
            $locations = $this->locationService->findLocationByExtService(LocationService::OKATO_SERVICE_CODE, $okato);
            if (!count($locations)) {
                $storage->setMoscowDistrictCode('');
                $storage->setCity(DeliveryService::MOSCOW_LOCATION_NAME);
                $storage->setCityCode(DeliveryService::MOSCOW_LOCATION_CODE);
                $this->orderStorageService->updateStorage($storage, $currentStep);
            } else {
                /**
                 * Обновляем storage, записываем зону
                 */
                $location = current($locations);
                $defaultCity = $storage->getCity();
                $defaultCityCode = $storage->getCityCode();
                $storage->setCity($location['NAME']);
                $storage->setCityCode($location['CODE']);
                $storage->setMoscowDistrictCode($location['CODE']);
                $this->orderStorageService->updateStorage($storage, $currentStep);

                /**
                 * Получаем цену доставки в этой зоне
                 */
                $innerDelivery = $this->orderStorageService->getInnerDelivery($storage);

                if (!$innerDelivery) {
                    $storage->setCity($defaultCity);
                    $storage->setCityCode($defaultCityCode);
                    $this->orderStorageService->updateStorage($storage, $currentStep);
                }
            }
        }

        if (!$innerDelivery) {
            $innerDelivery = $this->orderStorageService->getInnerDelivery($storage);
        }

        if ($innerDelivery == null) {
            $storage->setMoscowDistrictCode('');
            $storage->setCity(DeliveryService::MOSCOW_LOCATION_NAME);
            $storage->setCityCode(DeliveryService::MOSCOW_LOCATION_CODE);
            $this->orderStorageService->updateStorage($storage, $currentStep);
            return JsonErrorResponse::createWithData();
        }

        $deliveryPrice = $innerDelivery->getPrice();
        /** @var BasketService $basketService */
        $basketService = App::getInstance()->getContainer()->get(BasketService::class);
        $basket = $basketService->getBasket();
        $basketPrice = $basket->getPrice();
        /**
         * интервалы
         */
        $intervals = [];
        foreach ($innerDelivery->getAvailableIntervals() as $i => $interval) {
            $intervals[$i + 1] = [
                'text'     => (string)$interval,
                'selected' => ($i + 1 === 1) ? 'selected' : ''
            ];
        }
        /**
         * даты доставки
         */
        $nextDeliveries = $this->deliveryService->getNextDeliveries($innerDelivery, 10);
        $deliveryDates = [];
        foreach ($nextDeliveries as $i => $nextDelivery) {
            $deliveryTimestamp = $nextDelivery->getDeliveryDate()->getTimestamp();
            $deliveryDates[$i] = [
                'value'     => FormatDate('l, Y-m-d', $deliveryTimestamp),
                'text'     => FormatDate('l, d.m.Y', $deliveryTimestamp),
                'date'     => FormatDate('d.m.Y', $deliveryTimestamp),
                'selected' => ($i === 0) ? 'selected' : ''
            ];
        }

        return JsonSuccessResponse::createWithData(
            '',
            [
                'delivery_price'     => CurrencyHelper::formatPrice($deliveryPrice, false),
                'price_full'         => CurrencyHelper::formatPrice($basketPrice, false),
                'price_total'        => CurrencyHelper::formatPrice($basketPrice + $deliveryPrice, false),
                'deliver_date_price' => DeliveryTimeHelper::showTime($innerDelivery) . ', <span class="js-delivery--price">' . $deliveryPrice . '</span> ₽',
                'intervals'          => $intervals,
                'delivery_dates'     => $deliveryDates
            ]
        );
    }

    /**
     * @Route("/cancel/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     */
    public function orderCancelAction(Request $request): JsonResponse
    {
        $orderId = intval($request->request->get('orderId'));

        try {
            $cancelResult = $this->orderService->cancelOrder($orderId);
        } catch (OrderCancelException | \FourPaws\SaleBundle\Exception\NotFoundException  $e) {
            return JsonErrorResponse::createWithData('', ['errors' => [$e->getMessage()]]);
        } catch (\Exception $e) {
            return JsonErrorResponse::createWithData('', ['errors' => ['При отмене заказа произошла ошибка']]);
        }

        if (!$cancelResult) {
            return JsonErrorResponse::createWithData('', ['errors' => ['При отмене заказа произошла ошибка']]);
        }

        return JsonSuccessResponse::createWithData('Заказ успешно отменен', []);
    }

    /**
     * @Route("/extend/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     */
    public function orderExtendAction(Request $request): JsonResponse
    {
        $orderId = intval($request->request->get('orderId'));

        try {
            $extendResult = $this->orderService->extendOrder($orderId);
        } catch (OrderExtendException | \FourPaws\SaleBundle\Exception\NotFoundException  $e) {
            return JsonErrorResponse::createWithData('', ['errors' => [$e->getMessage()]]);
        } catch (\Exception $e) {
            return JsonErrorResponse::createWithData('', ['errors' => ['При продлении срока хранения произошла ошибка']]);
        }

        if (!$extendResult) {
            return JsonErrorResponse::createWithData('', ['errors' => ['При продлении срока хранения произошла ошибка']]);
        }

        return JsonSuccessResponse::createWithData('Срок хранения заказа успешно продлен', []);
    }
}
