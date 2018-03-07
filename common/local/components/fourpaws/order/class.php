<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\External\ManzanaPosService;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\SaleBundle\Service\UserAccountService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use Psr\Log\LoggerInterface;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderComponent extends \CBitrixComponent
{
    const DEFAULT_TEMPLATES_404 = [
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
     * @return $this
     */
    protected function prepareResult()
    {
        if ($this->currentStep === OrderStorageService::COMPLETE_STEP) {
            return $this;
        }

        if (!$storage = $this->orderStorageService->getStorage()) {
            throw new OrderCreateException('Failed to initialize storage');
        }

        try {
            $order = $this->orderService->createOrder($storage, false);
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
            $deliveries = $this->orderService->getDeliveries();
            foreach ($deliveries as $delivery) {
                $delivery->setCurrentDate($storage->getCurrentDate());
            }
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

            $this->arResult['PICKUP'] = $pickup;
            $this->arResult['DELIVERY'] = $delivery;
            $this->arResult['ADDRESSES'] = $addresses;
            $this->arResult['SELECTED_DELIVERY'] = $selectedDelivery;
            $this->arResult['SELECTED_DELIVERY_ID'] = $selectedDeliveryId;
        } elseif ($this->currentStep === OrderStorageService::PAYMENT_STEP) {
            $deliveries = $this->orderService->getDeliveries();
            $this->getPickupData($deliveries, $storage);
            $payments = $this->orderStorageService->getAvailablePayments($storage, true);
            $selectedDelivery = null;
            /** @var BaseResult $delivery */
            foreach ($deliveries as $delivery) {
                if ($delivery->getDeliveryId() !== $storage->getDeliveryId()) {
                    continue;
                }

                $selectedDelivery = $delivery;
            }

            if (!$selectedDelivery) {
                LocalRedirect(
                    $this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[OrderStorageService::DELIVERY_STEP]
                );
            }
            $this->arResult['SELECTED_DELIVERY'] = $selectedDelivery;

            $this->arResult['MAX_BONUS_SUM'] = 0;
            if ($user) {
                try {
                    $cheque = $this->manzanaPosService->processCheque(
                        $this->manzanaPosService->buildRequestFromBasket(
                            $basket,
                            $user->getDiscountCardNumber()
                        )
                    );
                    $this->arResult['MAX_BONUS_SUM'] = floor($cheque->getCardActiveBalance());
                } catch (ExecuteException $e) {
                    /* @todo выводить клиенту сообщение о невозможности оплаты бонусами? */
                    $this->logger->error($e->getMessage());
                }
            }
        }

        $this->arResult['USER'] = $user;
        $this->arResult['PAYMENTS'] = $payments;
        $this->arResult['SELECTED_CITY'] = $selectedCity;

        $this->arResult['METRO'] = $this->storeService->getMetroInfo();
        $this->arResult['BASKET'] = $basket;
        $this->arResult['STORAGE'] = $storage;
        $this->arResult['STEP'] = $this->currentStep;

        return $this;
    }

    /**
     * @param BaseResult[] $deliveries
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

        if (!$pickup) {
            return;
        }

        $selectedShopCode = $storage->getDeliveryPlaceCode();

        try {
            $shops = $pickup->getStockResult()->getStores(false);
        } catch (NotFoundException $e) {
            $this->logger->error(sprintf(
                    'Order has pickup delivery with no shops available. Delivery location: %s',
                    $storage->getCityCode())
            );
            return;
        }

        $selectedShop = null;
        if (!$selectedShopCode || !isset($shops[$selectedShopCode])) {
            /** @var Store $shop */
            foreach ($shops as $shop) {
                if ($pickup->getStockResult()->filterByStore($shop)->getDelayed()->isEmpty()) {
                    $selectedShop = $shop;
                    break;
                }
            }

            if (!$selectedShop) {
                $selectedShop = $shops->first();
            }
        } else {
            $selectedShop = $shops[$selectedShopCode];
        }

        $pickup->setStockResult($pickup->getStockResult()->filterByStore($selectedShop));
        $partialPickup = (clone $pickup)->setStockResult($pickup->getStockResult()->getAvailable());

        $this->arResult['SELECTED_SHOP'] = $selectedShop;
        $this->arResult['PARTIAL_PICKUP'] = $partialPickup;
    }
}
