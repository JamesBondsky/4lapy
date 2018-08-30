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
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\EcommerceBundle\Preset\Bitrix\SalePreset;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\ManzanaService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\PersonalBundle\Service\BonusService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Enum\OrderStorage as OrderStorageEnum;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\DeliveryNotAvailableException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Exception\OrderSplitException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\OrderSplitService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\SaleBundle\Service\UserAccountService;
use FourPaws\SaleBundle\Validation\OrderDeliveryValidator;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Exception\EmptyPhoneException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection EfferentObjectCouplingInspection */

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderComponent extends \CBitrixComponent
{
    protected const DEFAULT_TEMPLATES_404 = [
        OrderStorageEnum::AUTH_STEP => 'index.php',
        OrderStorageEnum::DELIVERY_STEP => 'delivery/',
        OrderStorageEnum::PAYMENT_STEP => 'payment/',
        OrderStorageEnum::COMPLETE_STEP => 'complete/#ORDER_ID#/',
    ];

    /**
     * @var string
     */
    protected $currentStep;
    /**
     * @var OrderService
     */
    protected $orderService;
    /**
     * @var OrderSplitService
     */
    protected $orderSplitService;
    /**
     * @var OrderStorageService
     */
    protected $orderStorageService;
    /**
     * @var DeliveryService
     */
    protected $deliveryService;
    /**
     * @var StoreService
     */
    protected $storeService;
    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;
    /**
     * @var UserCitySelectInterface
     */
    protected $userCityProvider;
    /**
     * @var BasketService $basketService
     */
    protected $basketService;
    /**
     * @var UserAccountService
     */
    protected $userAccountService;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var LocationService
     */
    protected $locationService;
    /**
     * @var ManzanaService
     */
    protected $manzanaService;
    /**
     * @var GoogleEcommerceService
     */
    private $ecommerceService;
    /**
     * @var SalePreset
     */
    private $salePreset;

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
        $this->orderSplitService = $serviceContainer->get(OrderSplitService::class);
        $this->orderStorageService = $serviceContainer->get(OrderStorageService::class);
        $this->deliveryService = $serviceContainer->get('delivery.service');
        $this->storeService = $serviceContainer->get('store.service');
        $this->currentUserProvider = $serviceContainer->get(CurrentUserProviderInterface::class);
        $this->userCityProvider = $serviceContainer->get(UserCitySelectInterface::class);
        $this->basketService = $serviceContainer->get(BasketService::class);
        $this->userAccountService = $serviceContainer->get(UserAccountService::class);
        $this->locationService = $serviceContainer->get('location.service');
        $this->manzanaService = $serviceContainer->get('manzana.service');
        $this->ecommerceService = $serviceContainer->get(GoogleEcommerceService::class);
        $this->salePreset = $serviceContainer->get(SalePreset::class);
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
        } catch (Exception $e) {
            try {
                $this->logger->error(sprintf('Component execute error: %s: %s', \get_class($e), $e->getMessage()), [
                    'trace' => $e->getTrace(),
                ]);
            } catch (RuntimeException $e) {
            }
        }

        parent::executeComponent();
    }

    /**
     * @return DeliveryService
     */
    public function getDeliveryService(): DeliveryService
    {
        return $this->deliveryService;
    }

    /**
     * @throws RuntimeException
     * @throws Exception
     * @throws OrderCreateException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws ApplicationCreateException
     */
    protected function prepareResult(): void
    {
        if ($this->currentStep === OrderStorageEnum::COMPLETE_STEP) {
            return;
        }

        if (!$storage = $this->orderStorageService->getStorage()) {
            throw new OrderCreateException('Failed to initialize storage');
        }

        $date = new \DateTime();
        if (($this->currentStep === OrderStorageEnum::DELIVERY_STEP) &&
            (abs(
                    $storage->getCurrentDate()->getTimestamp() - $date->getTimestamp()
                ) > OrderDeliveryValidator::MAX_DATE_DIFF)
        ) {
            $storage->setCurrentDate($date);
            $this->orderStorageService->updateStorage($storage, OrderStorageEnum::NOVALIDATE_STEP);
        }

        try {
            $order = $this->orderService->initOrder($storage);
        } catch (OrderCreateException $e) {
            LocalRedirect('/cart');

            return;
        }

        /** @var Basket $basket */
        $basket = $this->basketService->getBasket()->getOrderableItems();
        $this->arResult['ECOMMERCE_VIEW_SCRIPT'] = $this->getEcommerceViewScript($basket);
        /** @noinspection PhpUndefinedVariableInspection */
        if ($this->currentStep !== OrderStorageEnum::AUTH_STEP) {
            $basket = $order->getBasket();
        }

        $this->arResult['URL'] = [
            'AUTH' => $this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[OrderStorageEnum::AUTH_STEP],
            'DELIVERY' => $this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[OrderStorageEnum::DELIVERY_STEP],
            'PAYMENT' => $this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[OrderStorageEnum::PAYMENT_STEP],
        ];

        /** @var Router $router */
        $router = Application::getInstance()->getContainer()->get('router');
        /** @var Symfony\Component\Routing\RouteCollection $routeCollection */
        $routeCollection = $router->getRouteCollection();
        $routes = [
            'AUTH_VALIDATION' => 'fourpaws_sale_ajax_order_validateauth',
            'DELIVERY_VALIDATION' => 'fourpaws_sale_ajax_order_validatedelivery',
            'PAYMENT_VALIDATION' => 'fourpaws_sale_ajax_order_validatepayment',
            'BONUS_CARD_VALIDATION' => 'fourpaws_sale_ajax_order_validatebonuscard',
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

        $deliveries = $this->orderStorageService->getDeliveries($storage);
        $selectedDelivery = $this->orderStorageService->getSelectedDelivery($storage);
        if ($this->currentStep === OrderStorageEnum::DELIVERY_STEP) {
            $this->getPickupData($deliveries, $storage);

            $addresses = null;
            if ($storage->getUserId()) {
                /** @var AddressService $addressService */
                $addressService = Application::getInstance()->getContainer()->get('address.service');
                $addresses = $addressService->getAddressesByUser($storage->getUserId(), $selectedCity['CODE']);
            }

            $delivery = null;
            $pickup = null;
            foreach ($deliveries as $calculationResult) {
                if ($this->deliveryService->isPickup($calculationResult)) {
                    $pickup = $calculationResult;
                } elseif ($this->deliveryService->isDelivery($calculationResult)) {
                    $delivery = $calculationResult;
                }
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
        } elseif ($this->currentStep === OrderStorageEnum::PAYMENT_STEP) {
            $this->getPickupData($deliveries, $storage);

            try {
                if ($storage->isSplit()) {
                    $this->splitOrder($selectedDelivery, $storage);
                }
            } catch (OrderSplitException $e) {
                $this->logger->error(sprintf('failed to split order: %s', $e->getMessage()));
            }

            if ($user && !$user->getDiscountCardNumber()) {
                try {
                    $bonus = BonusService::getManzanaBonusInfo($user);
                    if (!$bonus->isEmpty()) {
                        $cardNumber = $this->manzanaService->prepareCardNumber($bonus->getCard()->getCardNumber());
                        $this->manzanaService->prepareCardNumber($bonus->getCard()->getCardNumber());
                        $this->currentUserProvider->getUserRepository()->updateDiscountCard(
                            $user->getId(),
                            $cardNumber
                        );
                        $user->setDiscountCardNumber($cardNumber);
                    }
                } catch (EmptyPhoneException $e) {
                    $this->logger->info('Нет телефона у пользователя - ' . $user->getId());
                } catch (ManzanaServiceException $e) {
                    $this->logger->error(sprintf('failed to get user discount card: %s', $e->getMessage()), [
                        'user' => $user->getId()
                    ]);
                }
            }

            $this->arResult['SELECTED_DELIVERY'] = $selectedDelivery;
            if ($this->arResult['PARTIAL_PICKUP_AVAILABLE'] &&
                $storage->isSplit() &&
                $this->deliveryService->isInnerPickup($selectedDelivery)
            ) {
                $this->arResult['SELECTED_DELIVERY'] = $this->arResult['PARTIAL_PICKUP'];
                /** @var Order $order1 */
                $order1 = $this->arResult['SPLIT_RESULT']['1']['ORDER'];
                $basket = $order1->getBasket();
            }

            if ($user) {
                $this->arResult['MAX_BONUS_SUM'] = $this->basketService->getMaxBonusesForPayment($basket);
            }

            $payments = $this->orderStorageService->getAvailablePayments($storage, true, true, $basket->getPrice());
        }

        $this->arResult['BASKET'] = $basket;
        $this->arResult['USER'] = $user;
        $this->arResult['PAYMENTS'] = $payments;
        $this->arResult['SELECTED_CITY'] = $selectedCity;
        $this->arResult['DADATA_CONSTRAINTS'] = $this->locationService->getDadataJsonFromLocationArray($selectedCity);

        $this->arResult['METRO'] = $this->storeService->getMetroInfo();
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
            $selectedShopCode = $storage->getDeliveryPlaceCode();
            $shops = $pickup->getStockResult()->getStores();
            if ($selectedShopCode && isset($shops[$selectedShopCode])) {
                $pickup->setSelectedShop($shops[$selectedShopCode]);
            }

            $this->arResult['SELECTED_SHOP'] = $pickup->getSelectedShop();

            if ($pickup->getSelectedShop()->getMetro()) {
                $this->arResult['METRO'] = $this->storeService->getMetroInfo(
                    ['ID' => $pickup->getSelectedShop()->getMetro()]
                );
            }
            $storage->setSplit(true);
            $storage->setDeliveryId($pickup->getDeliveryId());
            $storage->setDeliveryPlaceCode($pickup->getSelectedShop()->getXmlId());
            [$available, $delayed] = $this->orderSplitService->splitStockResult($pickup);

            $canGetPartial = $this->orderSplitService->canGetPartial($pickup);

            if ($canGetPartial) {
                $available = $this->orderSplitService->recalculateStockResult($available);
            }

            $this->arResult['PARTIAL_PICKUP'] = $available->isEmpty()
                ? null
                : (clone $pickup)->setStockResult($available);

            $this->arResult['PARTIAL_PICKUP_AVAILABLE'] = $canGetPartial;
            $this->arResult['SPLIT_PICKUP_AVAILABLE'] = $this->orderSplitService->canSplitOrder($pickup);
            $this->arResult['PICKUP_STOCKS_AVAILABLE'] = $available;
            $this->arResult['PICKUP_STOCKS_DELAYED'] = $delayed;
            $this->arResult['PICKUP_AVAILABLE_PAYMENTS'] = $this->orderStorageService->getAvailablePayments($storage, false, true, $pickup->getStockResult()->getPrice());
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
     * @throws SystemException
     * @throws UserMessageException
     * @throws LoaderException
     * @throws BitrixProxyException
     */
    protected function splitOrder(CalculationResultInterface $delivery, OrderStorage $storage): void
    {
        $tmpStorage = clone $storage;
        $tmpStorage->setDeliveryId($delivery->getDeliveryId());
        [$splitResult1, $splitResult2] = $this->orderSplitService->splitOrder($tmpStorage);
        $this->arResult['SPLIT_RESULT'] = [
            '1' => [
                'ORDER' => $splitResult1->getOrder(),
                'STORAGE' => $splitResult1->getOrderStorage(),
                'DELIVERY' => $splitResult1->getDelivery(),
            ],
            '2' => [
                'ORDER' => $splitResult2->getOrder(),
                'STORAGE' => $splitResult2->getOrderStorage(),
                'DELIVERY' => $splitResult2->getDelivery(),
            ],
        ];
    }

    /**
     * @param StockResultCollection $stockResultCollection
     * @return array
     */
    public function getOrderItemData(StockResultCollection $stockResultCollection): array
    {
        $itemData = [];
        $totalWeight = 0;
        /** @var StockResult $item */
        foreach ($stockResultCollection->getIterator() as $item) {
            $weight = $item->getOffer()->getCatalogProduct()->getWeight() * $item->getAmount();
            $offerId = $item->getOffer()->getId();
            $itemData[$offerId]['name'] = $item->getOffer()->getName();
            $itemData[$offerId]['quantity'] += $item->getAmount();
            $itemData[$offerId]['price'] += $item->getPrice();
            $itemData[$offerId]['weight'] += $weight;

            $totalWeight += $weight;
        }

        return [
            $itemData,
            $totalWeight,
        ];
    }

    public function getBasketItemData(Basket $basket)
    {
        $itemData = [];
        /** @var BasketItem $item */
        foreach ($basket as $item) {
            $offerId = (int)$item->getProductId();
            /** @var Offer $offer */
            foreach ($this->basketService->getOfferCollection() as $offer) {
                if ($offer->getId() === $offerId) {
                    $itemData[$offerId]['name'] = $item->getField('NAME');
                    $itemData[$offerId]['quantity'] += $item->getQuantity();
                    $itemData[$offerId]['price'] += $item->getPrice();
                    $itemData[$offerId]['weight'] += $offer->getCatalogProduct()->getWeight() * $item->getQuantity();
                    break;
                }
            }
        }

        return $itemData;
    }

    /**
     * @param Basket $basket
     *
     * @return string
     *
     * @throws RuntimeException
     */
    private function getEcommerceViewScript(Basket $basket): string
    {
        $script = '';
        $option = '';
        $step = 1;
        $isPreset = true;

        switch ($this->currentStep) {
            case OrderStorageEnum::COMPLETE_STEP:
                return $script;
            case OrderStorageEnum::AUTH_STEP:
                $step = 2;
                $option = 'Контактные данные';
                $isPreset = false;
                break;
            case OrderStorageEnum::DELIVERY_STEP:
                $step = 3;
                break;
            case OrderStorageEnum::PAYMENT_STEP:
                $step = 4;
                break;
        }

        $ecommerce = $this->salePreset->createEcommerceToCheckoutFromBasket($basket, $step, $option);

        return $isPreset
            ? $this->ecommerceService->renderPreset($ecommerce, 'preset', true)
            : $this->ecommerceService->renderScript($ecommerce, true);
    }
}
