<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Iblock\Component\Tools;
use FourPaws\App\Application;
use Bitrix\Sale\Delivery\CalculationResult;
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

    public function __construct($component = null)
    {
        $this->orderService = \FourPaws\App\Application::getInstance()->getContainer()->get(OrderService::class);
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

        /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router $router */
        $router = Application::getInstance()->getContainer()->get('router');
        /** @var Symfony\Component\Routing\RouteCollection $routeCollection */
        $routeCollection = $router->getRouteCollection();
        $routes = [
            'AUTH_VALIDATION'     => 'fourpaws_sale_ajax_order_validateauth',
            'DELIVERY_VALIDATION' => 'fourpaws_sale_ajax_order_validatedelivery',
            'PAYMENT_VALIDATION'  => 'fourpaws_sale_ajax_order_validatepayment',
        ];
        $ajaxUrl = [];
        foreach ($routes as $key => $name) {
            if (!$route = $routeCollection->get($name)) {
                continue;
            }
            $ajaxUrl[$key] = $route->getPath();
        }

        /** @var UserCitySelectInterface $userCityService */
        $userCityService = Application::getInstance()->getContainer()->get(UserCitySelectInterface::class);
        $selectedCity = $userCityService->getSelectedCity();

        $deliveries = [];
        $addresses = [];

        if ($this->currentStep === OrderService::DELIVERY_STEP) {
            $deliveries = $this->orderService->getDeliveries();

            if ($storage->getUserId()) {
                /** @var AddressService $addressService */
                $addressService = Application::getInstance()->getContainer()->get('address.service');
                $addresses = $addressService->getAddressesByUser($storage->getUserId(), $selectedCity['CODE']);
            }

            $this->getPickupData($deliveries, $storage);
        }

        $this->arResult['ORDER'] = $order;
        $this->arResult['METRO'] = $storeService->getMetroInfo();
        $this->arResult['BASKET'] = $basket;
        $this->arResult['STORAGE'] = $storage;
        $this->arResult['URL'] = $ajaxUrl;
        $this->arResult['SELECTED_CITY'] = $selectedCity;
        $this->arResult['ADDRESSES'] = $addresses;
        $this->arResult['DELIVERIES'] = $deliveries;

        return $this;
    }

    /**
     * @param array $deliveries
     * @param OrderStorage $storage
     */
    protected function getPickupData(array $deliveries, OrderStorage $storage)
    {
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        /** @var StoreService $storeService */
        $storeService = Application::getInstance()->getContainer()->get('store.service');
        $pickup = null;
        foreach ($deliveries as $calculationResult) {
            if ($deliveryService->isPickup($calculationResult)) {
                $pickup = $calculationResult;
            }
        }

        if (!$pickup) {
            return;
        }

        $partialPickup = clone $pickup;
        /** @var StockResultCollection $stockResult */
        $stockResult = $pickup->getData()['STOCK_RESULT'];

        if ($deliveryService->isDpdPickup($pickup)) {
            /* @todo получить терминалы DPD */
            // $shops = $deliveryService->getDpdTerminals();
        } else {
            $selectedShopCode = $storage->getDeliveryPlaceCode();
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

            $deliveryDate = $stockResult->getDeliveryDate();
            $partialDeliveryDate = $stockResult->getAvailable()->getDeliveryDate();

            $updateDeliveryDate = function (CalculationResult $pickup, \DateTime $deliveryDate) {
                $date = new \DateTime();
                if ($deliveryDate->format('z') !== $date->format('z')) {
                    $pickup->setPeriodType(CalculationResult::PERIOD_TYPE_DAY);
                    $pickup->setPeriodFrom($deliveryDate->format('z') - $date->format('z'));
                } else {
                    $pickup->setPeriodType(CalculationResult::PERIOD_TYPE_HOUR);
                    $pickup->setPeriodFrom($deliveryDate->format('G') - $date->format('G'));
                }
            };

            $updateDeliveryDate($pickup, $deliveryDate);
            $updateDeliveryDate($partialPickup, $partialDeliveryDate);
        }

        $this->arResult['SELECTED_SHOP'] = $selectedShop;
        $this->arResult['PARTIAL_PICKUP'] = $partialPickup;
    }
}
