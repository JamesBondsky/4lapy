<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Sale\Delivery\CalculationResult;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\UserBundle\Service\UserCitySelectInterface;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderComponent extends \CBitrixComponent
{
    const DEFAULT_TEMPLATES_404 = [
        OrderService::AUTH_STEP     => 'index.php',
        OrderService::DELIVERY_STEP => 'delivery/',
        OrderService::PAYMENT_STEP  => 'payment/',
        OrderService::COMPLETE_STEP => 'complete/',
    ];

    /**
     * @var string
     */
    protected $currentStep;

    /** @var OrderService */
    protected $orderService;

    /** @var DeliveryService */
    protected $deliveryService;

    public function __construct($component = null)
    {
        $this->orderService = Application::getInstance()->getContainer()->get(OrderService::class);
        $this->deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        parent::__construct($component);
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        global $APPLICATION;
        try {
            $variables = [];
            $componentPage = CComponentEngine::ParseComponentPath(
                $this->arParams['SEF_FOLDER'],
                self::DEFAULT_TEMPLATES_404,
                $variables
            );

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
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function prepareResult()
    {
        $serviceContainer = Application::getInstance()->getContainer();

        /** @var BasketService $basketService */
        $basketService = $serviceContainer->get(BasketService::class);
        /** @var StoreService $storeService */
        $storeService = $serviceContainer->get('store.service');
        $basket = $basketService->getBasket()->getOrderableItems();
        if ($basket->isEmpty()) {
            LocalRedirect('/cart');
        }

        $order = null;
        if (!$storage = $this->orderService->getStorage()) {
            throw new Exception('Failed to initialize storage');
        }

        $realStep = $this->orderService->validateStorage($storage, $this->currentStep);
        if ($realStep != $this->currentStep) {
            LocalRedirect($this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[$realStep]);
        }

        if ($this->currentStep === OrderService::COMPLETE_STEP) {
            /**
             * При переходе на страницу "спасибо за заказ" мы ищем заказ с переданным id
             */
            try {
                $order = $this->orderService->getById(
                    $this->arParams['ORDER_ID'],
                    true,
                    $storage->getUserId(),
                    $this->arParams['HASH']
                );
            } catch (NotFoundException $e) {
                Tools::process404('', true, true, true);
            }
        }

        $url = [
            'AUTH' => $this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[OrderService::AUTH_STEP],
            'DELIVERY' => $this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[OrderService::DELIVERY_STEP],
            'PAYMENT' => $this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[OrderService::PAYMENT_STEP],
        ];
        
        /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router $router */
        $router = Application::getInstance()->getContainer()->get('router');
        /** @var Symfony\Component\Routing\RouteCollection $routeCollection */
        $routeCollection = $router->getRouteCollection();
        $routes = [
            'AUTH_VALIDATION'     => 'fourpaws_sale_ajax_order_validateauth',
            'DELIVERY_VALIDATION' => 'fourpaws_sale_ajax_order_validatedelivery',
            'PAYMENT_VALIDATION'  => 'fourpaws_sale_ajax_order_validatepayment',
        ];
        foreach ($routes as $key => $name) {
            if (!$route = $routeCollection->get($name)) {
                continue;
            }
            $url[$key] = $route->getPath();
        }

        /** @var UserCitySelectInterface $userCityService */
        $userCityService = Application::getInstance()->getContainer()->get(UserCitySelectInterface::class);
        $selectedCity = $userCityService->getSelectedCity();

        $addresses = [];

        if ($this->currentStep === OrderService::DELIVERY_STEP) {
            $deliveries = $this->orderService->getDeliveries();

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
                $deliveryId = $calculationResult->getData()['DELIVERY_ID'];
                if (!$selectedDeliveryId) {
                    $selectedDeliveryId = $deliveryId;
                }

                if ($selectedDeliveryId === (int)$deliveryId) {
                    $selectedDelivery = $calculationResult;
                }

                $deliveryCode = $calculationResult->getData()['DELIVERY_CODE'];
                if (in_array($deliveryCode, DeliveryService::DELIVERY_CODES)) {
                    $delivery = $calculationResult;
                } elseif (in_array($deliveryCode, DeliveryService::PICKUP_CODES)) {
                    $pickup = $calculationResult;
                }
            }

            if (!$selectedDelivery) {
                $selectedDelivery = reset($deliveries);
                $selectedDeliveryId = (int)$selectedDelivery->getData()['DELIVERY_ID'];
            }

            $this->arResult['PICKUP'] = $pickup;
            $this->arResult['DELIVERY'] = $delivery;
            $this->arResult['SELECTED_DELIVERY'] = $selectedDelivery;
            $this->arResult['SELECTED_DELIVERY_ID'] = $selectedDeliveryId;

            $this->getPickupData($deliveries, $storage);
        }

        $this->arResult['ORDER'] = $order;
        $this->arResult['METRO'] = $storeService->getMetroInfo();
        $this->arResult['BASKET'] = $basket;
        $this->arResult['STORAGE'] = $storage;
        $this->arResult['URL'] = $url;
        $this->arResult['STEP'] = $this->currentStep;
        $this->arResult['SELECTED_CITY'] = $selectedCity;
        $this->arResult['ADDRESSES'] = $addresses;

        return $this;
    }

    /**
     * @param CalculationResult[] $deliveries
     * @param OrderStorage $storage
     */
    protected function getPickupData(array $deliveries, OrderStorage $storage)
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

        $partialPickup = clone $pickup;
        /** @var StockResultCollection $stockResult */
        $stockResult = $this->deliveryService->getStockResultByDelivery($pickup);
        if ($this->deliveryService->isDpdPickup($pickup)) {
            $selectedShopCode = $storage->getDpdTerminalCode();
        } else {
            $selectedShopCode = $storage->getDeliveryPlaceCode();
        }
        $shops = $stockResult->getStores();

        $selectedShop = null;
        if (!$selectedShopCode || !isset($shops[$selectedShopCode])) {
            /** @var Store $shop */
            foreach ($shops as $shop) {
                if ($stockResult->filterByStore($shop)->getDelayed()->isEmpty()) {
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

        if ($this->deliveryService->isInnerPickup($pickup)) {
            $deliveryDate = $stockResult->getDeliveryDate();
            $partialDeliveryDate = $stockResult->getAvailable()->getDeliveryDate();

            DeliveryTimeHelper::updateDeliveryDate($pickup, $deliveryDate);
            DeliveryTimeHelper::updateDeliveryDate($partialPickup, $partialDeliveryDate);
        }

        $this->arResult['SELECTED_SHOP'] = $selectedShop;
        $this->arResult['PARTIAL_PICKUP'] = $partialPickup;
    }
}
