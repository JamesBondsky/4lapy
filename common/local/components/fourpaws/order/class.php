<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Order;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\ManzanaPosService;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\DeliveryNotAvailableException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Exception\OrderSplitException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\SaleBundle\Service\UserAccountService;
use FourPaws\SaleBundle\Validation\OrderDeliveryValidator;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderComponent extends \CBitrixComponent
{
    protected const DEFAULT_TEMPLATES_404 = [
        OrderStorageService::AUTH_STEP => 'index.php',
        OrderStorageService::DELIVERY_STEP => 'delivery/',
        OrderStorageService::PAYMENT_STEP => 'payment/',
        OrderStorageService::COMPLETE_STEP => 'complete/#ORDER_ID#',
    ];

    /** @var string */
    protected $currentStep;

    /** @var OrderService */
    protected $orderService;

    /** @var OrderStorageService */
    protected $orderStorageService;

    /** @var DeliveryService */
    protected $deliveryService;

    /** @var StoreService */
    protected $storeService;

    /** @var CurrentUserProviderInterface */
    protected $currentUserProvider;

    /** @var UserCitySelectInterface */
    protected $userCityProvider;

    /** @var BasketService $basketService */
    protected $basketService;

    /** @var UserAccountService */
    protected $userAccountService;

    /** @var ManzanaPosService */
    protected $manzanaPosService;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * FourPawsOrderComponent constructor.
     *
     * @param $component
     *
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function __construct($component = null)
    {
        $serviceContainer = Application::getInstance()->getContainer();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->orderService = $serviceContainer->get(OrderService::class);
        $this->orderStorageService = $serviceContainer->get(OrderStorageService::class);
        $this->deliveryService = $serviceContainer->get('delivery.service');
        $this->storeService = $serviceContainer->get('store.service');
        $this->currentUserProvider = $serviceContainer->get(CurrentUserProviderInterface::class);
        $this->userCityProvider = $serviceContainer->get(UserCitySelectInterface::class);
        $this->basketService = $serviceContainer->get(BasketService::class);
        $this->userAccountService = $serviceContainer->get(UserAccountService::class);
        $this->manzanaPosService = $serviceContainer->get('manzana.pos.service');
        $this->logger = LoggerFactory::create('component_order');

        parent::__construct($component);
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        global $APPLICATION;
        try {
            $variables = [];
            $componentPage = CComponentEngine::parseComponentPath(
                $this->arParams['SEF_FOLDER'],
                self::DEFAULT_TEMPLATES_404,
                $variables
            );

            foreach ($variables as $code => $value) {
                $this->arParams[$code] = $value;
            }

            if (!$componentPage) {
                LocalRedirect($this->arParams['SEF_FOLDER']);
            }

            $this->currentStep = $componentPage;

            if ($this->arParams['SET_TITLE'] === 'Y') {
                $APPLICATION->SetTitle('Оформление заказа');
            }

            $this->prepareResult();

            $this->includeComponentTemplate($componentPage);
        } catch (\Exception $e) {
            try {
                $this->logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }

    /**
     * @throws Exception
     * @throws OrderCreateException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws ApplicationCreateException
     */
    protected function prepareResult(): void
    {
        if ($this->currentStep === OrderStorageService::COMPLETE_STEP) {
            return;
        }

        if (!$storage = $this->orderStorageService->getStorage()) {
            throw new OrderCreateException('Failed to initialize storage');
        }

        $date = new \DateTime();
        if (abs(
                $storage->getCurrentDate()->getTimestamp() - $date->getTimestamp()
            ) > OrderDeliveryValidator::MAX_DATE_DIFF
        ) {
            $storage->setCurrentDate($date);
            $this->orderStorageService->updateStorage($storage);
        }

        try {
            $order = $this->orderService->initOrder($storage);
        } catch (OrderCreateException $e) {
            LocalRedirect('/cart');
        }

        /** @noinspection PhpUndefinedVariableInspection */
        $basket = $order->getBasket()->getOrderableItems();

        $this->arResult['URL'] = [
            'AUTH' => $this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[OrderStorageService::AUTH_STEP],
            'DELIVERY' => $this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[OrderStorageService::DELIVERY_STEP],
            'PAYMENT' => $this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[OrderStorageService::PAYMENT_STEP],
        ];

        /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router $router */
        $router = Application::getInstance()->getContainer()->get('router');
        /** @var Symfony\Component\Routing\RouteCollection $routeCollection */
        $routeCollection = $router->getRouteCollection();
        $routes = [
            'AUTH_VALIDATION' => 'fourpaws_sale_ajax_order_validateauth',
            'DELIVERY_VALIDATION' => 'fourpaws_sale_ajax_order_validatedelivery',
            'PAYMENT_VALIDATION' => 'fourpaws_sale_ajax_order_validatepayment',
            'DELIVERY_INTERVALS' => 'fourpaws_sale_ajax_order_deliveryintervals',
        ];
        foreach ($routes as $key => $name) {
            /** @noinspection NullPointerExceptionInspection */
            if (!$route = $routeCollection->get($name)) {
                continue;
            }
            $this->arResult['URL'][$key] = $route->getPath();
        }

        $realStep = $this->orderStorageService->validateStorage($storage, $this->currentStep);
        if ($realStep !== $this->currentStep) {
            LocalRedirect($this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[$realStep]);
        }

        $selectedCity = $this->userCityProvider->getSelectedCity();

        $payments = null;

        $user = null;
        try {
            $user = $this->currentUserProvider->getCurrentUser();
        } catch (NotAuthorizedException $e) {
        }

        if ($this->currentStep === OrderStorageService::DELIVERY_STEP) {
            $deliveries = $this->orderStorageService->getDeliveries($storage);
            $this->getPickupData($deliveries, $storage);

            $addresses = null;
            if ($storage->getUserId()) {
                /** @var AddressService $addressService */
                $addressService = Application::getInstance()->getContainer()->get('address.service');
                $addresses = $addressService->getAddressesByUser($storage->getUserId(), $selectedCity['CODE']);
            }

            $delivery = null;
            $pickup = null;
            $selectedDelivery = null;
            $selectedDeliveryId = $storage->getDeliveryId();
            foreach ($deliveries as $calculationResult) {
                $deliveryId = $calculationResult->getDeliveryId();
                if (!$selectedDeliveryId) {
                    $selectedDeliveryId = $deliveryId;
                }

                if ($selectedDeliveryId === $deliveryId) {
                    $selectedDelivery = $calculationResult;
                }

                if ($this->deliveryService->isPickup($calculationResult)) {
                    $pickup = $calculationResult;
                } elseif ($this->deliveryService->isDelivery($calculationResult)) {
                    $delivery = $calculationResult;
                }
            }

            if (!$selectedDelivery) {
                $selectedDelivery = reset($deliveries);
                $selectedDeliveryId = (int)$selectedDelivery->getDeliveryId();
            }

            try {
                if (null !== $delivery) {
                    $this->splitOrder($delivery, $storage);
                }
            } catch (OrderSplitException $e) {
                // проверяется на этапе валидации $storage
            }

            $this->arResult['PICKUP'] = $pickup;
            $this->arResult['DELIVERY'] = $delivery;
            $this->arResult['ADDRESSES'] = $addresses;
            $this->arResult['SELECTED_DELIVERY'] = $selectedDelivery;
            $this->arResult['SELECTED_DELIVERY_ID'] = $selectedDeliveryId;
        } elseif ($this->currentStep === OrderStorageService::PAYMENT_STEP) {
            $deliveries = $this->orderStorageService->getDeliveries($storage);
            $this->getPickupData($deliveries, $storage);
            $payments = $this->orderStorageService->getAvailablePayments($storage, true);
            $selectedDelivery = null;
            /** @var CalculationResultInterface $delivery */
            foreach ($deliveries as $delivery) {
                if ($delivery->getDeliveryId() !== $storage->getDeliveryId()) {
                    continue;
                }

                $selectedDelivery = $delivery;
            }

            try {
                if ($storage->isSplit()) {
                    $this->splitOrder($selectedDelivery, $storage);
                }
            } catch (OrderSplitException $e) {
                $this->logger->error(sprintf('failed to split order: %s', $e->getMessage()));
            }

            $this->arResult['SELECTED_DELIVERY'] = $selectedDelivery;

            $this->arResult['MAX_BONUS_SUM'] = 0;
            if ($user) {
                $basketForRequest = $basket;
                if ($storage->isSplit() && $this->orderStorageService->canGetPartial($selectedDelivery)) {
                    /** @var Order $order1 */
                    $order1 = $this->arResult['SPLIT_RESULT']['1']['ORDER'];
                    $basketForRequest = $order1->getBasket();
                }

                $this->arResult['MAX_BONUS_SUM'] = $this->basketService->getMaxBonusesForPayment($basketForRequest);
            }
        }

        $this->arResult['USER'] = $user;
        $this->arResult['PAYMENTS'] = $payments;
        $this->arResult['SELECTED_CITY'] = $selectedCity;

        $this->arResult['METRO'] = $this->storeService->getMetroInfo();
        $this->arResult['BASKET'] = $basket;
        $this->arResult['STORAGE'] = $storage;
        $this->arResult['STEP'] = $this->currentStep;
    }

    /**
     * @param CalculationResultInterface[] $deliveries
     * @param OrderStorage $storage
     */
    protected function getPickupData(array $deliveries, OrderStorage $storage): void
    {
        $pickup = null;
        foreach ($deliveries as $calculationResult) {
            if ($this->deliveryService->isPickup($calculationResult)) {
                $pickup = $calculationResult;
            }
        }

        if (null !== $pickup) {
            /** @var PickupResultInterface $pickup */
            $storage = clone $storage;
            try {
                $selectedShopCode = $storage->getDeliveryPlaceCode();
                $shops = $pickup->getStockResult()->getStores();
                if ($selectedShopCode && isset($shops[$selectedShopCode])) {
                    $pickup->setSelectedStore($shops[$selectedShopCode]);
                }

                $this->arResult['SELECTED_SHOP'] = $pickup->getSelectedShop();
            } catch (NotFoundException $e) {
                $this->logger->error(sprintf(
                        'Order has pickup delivery with no shops available. Delivery location: %s',
                        $storage->getCityCode())
                );
                return;
            }
            $storage->setSplit(true);
            $storage->setDeliveryId($pickup->getDeliveryId());

            [$available, $delayed] = $this->orderStorageService->splitStockResult($pickup);
            $this->arResult['PARTIAL_PICKUP'] = $available->isEmpty()
                ? null
                : (clone $pickup)->setStockResult($available);
            $this->arResult['PARTIAL_PICKUP_AVAILABLE'] = $this->orderStorageService->canGetPartial($pickup);
            $this->arResult['SPLIT_PICKUP_AVAILABLE'] = $this->orderStorageService->canSplitOrder($pickup);
            $this->arResult['PICKUP_STOCKS_AVAILABLE'] = $available;
            $this->arResult['PICKUP_STOCKS_DELAYED'] = $delayed;
        }
    }

    /**
     * @param CalculationResultInterface $delivery
     * @param OrderStorage $storage
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws DeliveryNotAvailableException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderCreateException
     * @throws OrderSplitException
     * @throws StoreNotFoundException
     * @throws UserMessageException
     * @throws SystemException
     */
    protected function splitOrder(CalculationResultInterface $delivery, OrderStorage $storage)
    {
        $tmpStorage = clone $storage;
        $tmpStorage->setDeliveryId($delivery->getDeliveryId());
        [$splitResult1, $splitResult2] = $this->orderService->splitOrder($tmpStorage);
        $this->arResult['SPLIT_RESULT'] = [
            '1' => [
                'ORDER' => $splitResult1->getOrder(),
                'STORAGE' => $splitResult1->getOrderStorage(),
                'DELIVERY' => $splitResult1->getDelivery()
            ],
            '2' => [
                'ORDER' => $splitResult2->getOrder(),
                'STORAGE' => $splitResult2->getOrderStorage(),
                'DELIVERY' => $splitResult2->getDelivery()
            ]
        ];
    }
}
