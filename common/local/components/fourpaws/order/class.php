<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use FourPaws\UserBundle\Service\UserCitySelectInterface;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderComponent extends \CBitrixComponent
{
    const DEFAULT_TEMPLATES_404 = [
        OrderStorageService::AUTH_STEP     => 'index.php',
        OrderStorageService::DELIVERY_STEP => 'delivery/',
        OrderStorageService::PAYMENT_STEP  => 'payment/',
        OrderStorageService::COMPLETE_STEP => 'complete/#ORDER_ID#',
    ];

    /**
     * @var string
     */
    protected $currentStep;

    /** @var OrderService */
    protected $orderService;

    /** @var OrderStorageService */
    protected $orderStorageService;

    /** @var DeliveryService */
    protected $deliveryService;

    /** @var StoreService */
    protected $storeService;

    /** @var UserAuthorizationInterface */
    protected $userAuthProvider;

    /** @var UserCitySelectInterface */
    protected $userCityProvider;

    /** @var BasketService $basketService */
    protected $basketService;

    public function __construct($component = null)
    {
        $serviceContainer = Application::getInstance()->getContainer();
        $this->orderService = $serviceContainer->get(OrderService::class);
        $this->orderStorageService = $serviceContainer->get(OrderStorageService::class);
        $this->deliveryService = $serviceContainer->get('delivery.service');
        $this->storeService = $serviceContainer->get('store.service');
        $this->userAuthProvider = $serviceContainer->get(UserAuthorizationInterface::class);
        $this->userCityProvider = $serviceContainer->get(UserCitySelectInterface::class);
        $this->basketService = $serviceContainer->get(BasketService::class);
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
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
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
        $basket = $this->basketService->getBasket()->getOrderableItems();

        $order = null;
        if (!$storage = $this->orderStorageService->getStorage()) {
            throw new Exception('Failed to initialize storage');
        }

        $this->arResult['URL'] = [
            'AUTH'     => $this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[OrderStorageService::AUTH_STEP],
            'DELIVERY' => $this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[OrderStorageService::DELIVERY_STEP],
            'PAYMENT'  => $this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[OrderStorageService::PAYMENT_STEP],
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
            $this->arResult['URL'][$key] = $route->getPath();
        }

        if ($this->currentStep === OrderStorageService::COMPLETE_STEP) {
            /**
             * При переходе на страницу "спасибо за заказ" мы ищем заказ с переданным id
             */
            try {
                $order = $this->orderService->getOrderById(
                    $this->arParams['ORDER_ID'],
                    true,
                    $storage->getUserId(),
                    $this->arParams['HASH']
                );
            } catch (NotFoundException $e) {
                Tools::process404('', true, true, true);
            }

            /**
             * Попытка открыть уже обработанный заказ
             */
            if (!\in_array(
                    $order->getField('STATUS_ID'),
                    [
                        OrderService::STATUS_NEW_COURIER,
                        OrderService::STATUS_NEW_PICKUP
                    ]
                )
            ) {
                Tools::process404('', true, true, true);
            }

            $this->arResult['ORDER'] = $order;
            $this->arResult['ORDER_PROPERTIES'] = [];
            /**
             * флаг, что пользователь был зарегистрирован при оформлении заказа
             */
            $this->arResult['ORDER_REGISTERED'] = !$this->userAuthProvider->isAuthorized();

            /** @var PropertyValue $propertyValue */
            foreach ($order->getPropertyCollection() as $propertyValue) {
                $this->arResult['ORDER_PROPERTIES'][$propertyValue->getProperty()['CODE']] = $propertyValue->getValue();
            }

            /** @var Shipment $shipment */
            if ($shipment = $order->getShipmentCollection()->current()) {
                $this->arResult['ORDER_DELIVERY'] = $this->getDeliveryData($this->arResult['ORDER_PROPERTIES']);
                $this->arResult['ORDER_DELIVERY']['DELIVERY_CODE'] = $shipment->getDelivery()->getCode();
            }
        } else {
            if ($basket->isEmpty()) {
                LocalRedirect('/cart');
            }
            $realStep = $this->orderStorageService->validateStorage($storage, $this->currentStep);
            if ($realStep !== $this->currentStep) {
                LocalRedirect($this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[$realStep]);
            }

            $selectedCity = $this->userCityProvider->getSelectedCity();

            $payments = null;
            if ($this->currentStep === OrderStorageService::DELIVERY_STEP) {
                $deliveries = $this->orderService->getDeliveries();

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
                    $deliveryId = $calculationResult->getData()['DELIVERY_ID'];
                    if (!$selectedDeliveryId) {
                        $selectedDeliveryId = $deliveryId;
                    }

                    if ($selectedDeliveryId === (int)$deliveryId) {
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
                    $selectedDeliveryId = (int)$selectedDelivery->getData()['DELIVERY_ID'];
                }

                $this->arResult['PICKUP'] = $pickup;
                $this->arResult['DELIVERY'] = $delivery;
                $this->arResult['ADDRESSES'] = $addresses;
                $this->arResult['SELECTED_DELIVERY'] = $selectedDelivery;
                $this->arResult['SELECTED_DELIVERY_ID'] = $selectedDeliveryId;

                $this->getPickupData($deliveries, $storage);
            } elseif ($this->currentStep === OrderStorageService::PAYMENT_STEP) {
                $deliveries = $this->orderService->getDeliveries();
                $payments = $this->orderStorageService->getAvailablePayments($storage, true);
                $selectedDelivery = null;
                /** @var CalculationResult $delivery */
                foreach ($deliveries as $delivery) {
                    if ((int)$delivery->getData()['DELIVERY_ID'] !== $storage->getDeliveryId()) {
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
            }

            $this->arResult['PAYMENTS'] = $payments;
            $this->arResult['SELECTED_CITY'] = $selectedCity;
        }

        $this->arResult['METRO'] = $this->storeService->getMetroInfo();
        $this->arResult['BASKET'] = $basket;
        $this->arResult['STORAGE'] = $storage;
        $this->arResult['STEP'] = $this->currentStep;

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

        if ($this->deliveryService->isInnerPickup($pickup)) {
            $deliveryDate = $stockResult->getDeliveryDate();
            $partialDeliveryDate = $stockResult->getAvailable()->getDeliveryDate();

            DeliveryTimeHelper::updateDeliveryDate($pickup, $deliveryDate);
            DeliveryTimeHelper::updateDeliveryDate($partialPickup, $partialDeliveryDate);
        }

        $this->arResult['SELECTED_SHOP'] = $selectedShop;
        $this->arResult['PARTIAL_PICKUP'] = $partialPickup;
    }

    /**
     * @param array $properties
     *
     * @return array
     */
    protected function getDeliveryData(array $properties): array
    {
        $result = [];
        if ($properties['DELIVERY_PLACE_CODE']) {
            try {
                $store = $this->storeService->getByXmlId($properties['DELIVERY_PLACE_CODE']);
                $result['ADDRESS'] = $store->getAddress();
                $result['SCHEDULE'] = $store->getSchedule();
            } catch (StoreNotFoundException $e) {
            }
        } elseif ($properties['DPD_TERMINAL_CODE']) {
            $terminals = $this->deliveryService->getDpdTerminalsByLocation($properties['CITY_CODE']);
            if ($terminal = $terminals[$properties['DPD_TERMINAL_CODE']]) {
                $result['ADDRESS'] = $terminal->getAddress();
                $result['SCHEDULE'] = $terminal->getSchedule();
            }
        } else {
            $result['ADDRESS'] = [
                $properties['CITY'],
                $properties['STREET'],
                $properties['HOUSE'],
            ];
            if (!empty($properties['BUILDING'])) {
                $result['ADDRESS'][] = 'корпус ' . $properties['BUILDING'];
            }
            if (!empty($properties['PORCH'])) {
                $result['ADDRESS'][] = 'подъезд ' . $properties['PORCH'];
            }
            if (!empty($properties['FLOOR'])) {
                $result['ADDRESS'][] = 'этаж ' . $properties['FLOOR'];
            }
            if (!empty($properties['APARTMENT'])) {
                $result['ADDRESS'][] = 'кв. ' . $properties['APARTMENT'];
            }
            $result['ADDRESS'] = implode(', ', $result['ADDRESS']);
        }

        if ($properties['DELIVERY_DATE']) {
            $match = [];
            $deliveryString = $properties['DELIVERY_DATE'];
            if (preg_match('~^(\d{2}):\d{2}~', $properties['DELIVERY_INTERVAL'], $match)) {
                $deliveryString .= ' ' . $match[1] . ':00';
            } else {
                $deliveryString .= ' 00:00';
            }

            $result['DELIVERY_DATE'] = \DateTime::createFromFormat('d.m.Y H:i', $deliveryString);
        }

        if ($properties['DELIVERY_INTERVAL']) {
            $result['DELIVERY_INTERVAL'] = $properties['DELIVERY_INTERVAL'];
        }

        return $result;
    }
}
