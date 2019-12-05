<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Internals\BasketTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DobrolapDeliveryResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\LocationNotFoundException;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\EcommerceBundle\Preset\Bitrix\SalePreset;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\EcommerceBundle\Service\RetailRocketService;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\ManzanaService;
use FourPaws\KioskBundle\Service\KioskService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\PersonalBundle\Service\BonusService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\PersonalBundle\Service\PiggyBankService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Enum\OrderStorage as OrderStorageEnum;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\DeliveryNotAvailableException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Exception\OrderSplitException;
use FourPaws\SaleBundle\Repository\Table\AnimalShelterTable;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\OrderSplitService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\SaleBundle\Service\ShopInfoService;
use FourPaws\SaleBundle\Service\UserAccountService;
use FourPaws\SaleBundle\Validation\OrderDeliveryValidator;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Entity\User;
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
        OrderStorageEnum::INTERVIEW_STEP => 'interview/#ORDER_ID#/',
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
     * @var ShopInfoService
     */
    protected $shopListService;
    /**
     * @var GoogleEcommerceService
     */
    private $ecommerceService;
    /**
     * @var SalePreset
     */
    private $salePreset;
    /**
     * @var RetailRocketService
     */
    private $retailRocketService;

    /** @var OrderSubscribeService $orderSubscribeService */
    private $orderSubscribeService;

    /** @var KioskService $kioskService */
    private $kioskService;

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
        $container = Application::getInstance()->getContainer();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->orderService          = $container->get(OrderService::class);
        $this->orderSplitService     = $container->get(OrderSplitService::class);
        $this->orderStorageService   = $container->get(OrderStorageService::class);
        $this->orderSubscribeService = $container->get('order_subscribe.service');
        $this->shopListService       = $container->get(ShopInfoService::class);
        $this->deliveryService       = $container->get('delivery.service');
        $this->storeService          = $container->get('store.service');
        $this->currentUserProvider   = $container->get(CurrentUserProviderInterface::class);
        $this->userCityProvider      = $container->get(UserCitySelectInterface::class);
        $this->basketService         = $container->get(BasketService::class);
        $this->userAccountService    = $container->get(UserAccountService::class);
        $this->locationService       = $container->get('location.service');
        $this->manzanaService        = $container->get('manzana.service');
        $this->ecommerceService      = $container->get(GoogleEcommerceService::class);
        $this->salePreset            = $container->get(SalePreset::class);
        $this->retailRocketService   = $container->get(RetailRocketService::class);
        $this->logger                = LoggerFactory::create('component_order');
        $this->kioskService          = $container->get('kiosk.service');

        parent::__construct($component);
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        global $APPLICATION;

        try {
            $variables     = [];
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

            $this->prepareResult();


            if ($this->arParams['SET_TITLE'] === 'Y') {
                if($this->orderStorageService->getStorage()->isSubscribe()){
                    $APPLICATION->SetTitle('Оформление подписки на доставку');
                } else {
                    $APPLICATION->SetTitle('Оформление заказа');
                }
            }

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
        if ($this->currentStep === OrderStorageEnum::COMPLETE_STEP ||
            $this->currentStep === OrderStorageEnum::INTERVIEW_STEP
        ) {
            return;
        }

        if (!$storage = $this->orderStorageService->getStorage()) {
            throw new OrderCreateException('Failed to initialize storage');
        }


        if ($this->currentStep === OrderStorageEnum::AUTH_STEP) {

            // режим подписки на доставку
            if ($_POST['subscribe'] && ($_POST['orderId'] > 0 || !$storage->isSubscribe())) {

                // переход из истории заказов иммитирует добавление всех товаров из заказа
                $orderId = (int)$_POST['orderId'];
                if($orderId > 0){
                    $basket = new CSaleBasket();
                    $basket->DeleteAll(CSaleBasket::GetBasketUserID());

                    $dbres = BasketTable::getList([
                        'select' => ['PRODUCT_ID', 'QUANTITY'],
                        'filter' => ['ORDER_ID' => $orderId]
                    ]);

                    /** @var PiggyBankService $piggyBankService */
                    $piggyBankService = Application::getInstance()->getContainer()->get('piggy_bank.service');

                    while($basketItem = $dbres->fetch()){
                        if (in_array($basketItem['PRODUCT_ID'], $piggyBankService->getMarksIds(), false))
                            continue;

                        $this->basketService->addOfferToBasket($basketItem['PRODUCT_ID'], $basketItem['QUANTITY']);
                    }

                    // если ранее была создана подписка - удаляем
                    if($storage->getSubscribeId() > 0){
                        $this->getOrderSubscribeService()->delete($storage->getSubscribeId());
                        $storage->setSubscribeId(null);
                    }
                }

                $storage->setSubscribe(true);
                $this->orderStorageService->updateStorage($storage, OrderStorageEnum::NOVALIDATE_STEP);
            }
            elseif ($storage->isSubscribe() && $_POST['default']) {
                if($storage->getSubscribeId() > 0){
                    $this->getOrderSubscribeService()->delete($storage->getSubscribeId());
                    $storage->setSubscribeId(null);
                }
                $storage->setSubscribe(false);
                $this->orderStorageService->updateStorage($storage, OrderStorageEnum::NOVALIDATE_STEP);
            }

            // капча в киоске не нужна
            if (KioskService::isKioskMode() && !$storage->isCaptchaFilled()) {
                $storage->setCaptchaFilled(true);
                $this->orderStorageService->updateStorage($storage, OrderStorageEnum::NOVALIDATE_STEP);
            }
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

        /**
         * Moscow Districts
         */
        if (($this->currentStep === OrderStorageEnum::PAYMENT_STEP) && $storage->getMoscowDistrictCode() != '') {
            $this->orderStorageService->updateStorageMoscowZone($storage, OrderStorageEnum::NOVALIDATE_STEP);
        }

        try {
            $order = $this->orderService->initOrder($storage, null, null, ($this->currentStep !== OrderStorageEnum::PAYMENT_STEP));
        } catch (OrderCreateException | \FourPaws\SaleBundle\Exception\NotFoundException $e) {
            if ($this->currentStep === OrderStorageEnum::PAYMENT_STEP && $_SESSION['ORDER_PAYMENT_URL']) {
                $url = $_SESSION['ORDER_PAYMENT_URL'];
                unset($_SESSION['ORDER_PAYMENT_URL']);
                LocalRedirect($url);
            }

            LocalRedirect('/cart');

            return;
        } catch (LocationNotFoundException $e) {
            /* ошибка от экспресс доставки 4 лап выпадает только на последнем шаге */
            if ($this->currentStep === OrderStorageEnum::PAYMENT_STEP) {
                $storage->setDeliveryId(0);
                $this->orderStorageService->updateStorage($storage, OrderStorageEnum::NOVALIDATE_STEP);
                LocalRedirect('/sale/order/delivery');
            }
        }

        $user = null;
        try {
            $user = $this->currentUserProvider->getCurrentUser();
        } catch (NotAuthorizedException $e) {
        }

        /** @var Basket $basket */
        $basket                                  = $this->basketService->getBasket()->getOrderableItems();
        $this->arResult['ECOMMERCE_VIEW_SCRIPT'] = $this->getEcommerceViewScript($basket);
        /** @noinspection PhpUndefinedVariableInspection */
        if ($this->currentStep === OrderStorageEnum::AUTH_STEP) {
            $this->arResult['ON_SUBMIT'] = \str_replace('"', '\'',
                'if($(this).find("input[type=email]").val().indexOf("register.phone") == -1){' . $this->retailRocketService->renderSendEmail('$(this).find("input[type=email]").val()') . '}'
            );

            if ($user)
            {
                /** @var BonusService $bonusService */
                $bonusService = Application::getInstance()->getContainer()->get('bonus.service');
                $bonusService->updateUserBonusInfo($user); //TODO need async here
            }
        } else {
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
        $routes          = [
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
            if ($this->currentStep === OrderStorageEnum::PAYMENT_STEP && $_SESSION['ORDER_PAYMENT_URL']) {
                $url = $_SESSION['ORDER_PAYMENT_URL'];
                unset($_SESSION['ORDER_PAYMENT_URL']);
                LocalRedirect($url);
            }
            LocalRedirect($this->arParams['SEF_FOLDER'] . self::DEFAULT_TEMPLATES_404[$realStep]);
        }

        $selectedCity = $this->userCityProvider->getSelectedCity();

        $payments = null;

        $deliveries       = $this->orderStorageService->getDeliveries($storage);

        $selectedDelivery = $this->orderStorageService->getSelectedDelivery($storage);

        if ($this->currentStep === OrderStorageEnum::DELIVERY_STEP) {
            $this->getPickupData($deliveries, $storage);

            $addresses = null;
            if ($storage->getUserId()) {
                /** @var AddressService $addressService */
                $addressService = Application::getInstance()->getContainer()->get('address.service');
                $addresses      = $addressService->getAddressesByUser($storage->getUserId(), $selectedCity['CODE']);
            }

            $delivery = null;
            $pickup   = null;
            $deliveryDostavista = null;
            $expressDelivery = null;
            $deliveryDobrolap = null;
            foreach ($deliveries as $calculationResult) {
                if ($this->deliveryService->isPickup($calculationResult)) {
                    $pickup = $calculationResult;
                } elseif (!$delivery && $this->deliveryService->isDelivery($calculationResult)) {
                    $delivery = $calculationResult;
                } elseif ($this->deliveryService->isDostavistaDelivery($calculationResult)) {
                    $deliveryDostavista = $calculationResult;
                } elseif ($this->deliveryService->isDobrolapDelivery($calculationResult)) {
                    $deliveryDobrolap = $calculationResult;
                    $this->getDobrolapData($deliveries, $storage, $selectedCity);
                } elseif ($this->deliveryService->isExpressDelivery($calculationResult)) {
                    $expressDelivery = $calculationResult;
                }
            }

            try {
                if (null !== $delivery && !$storage->isSubscribe()) {
                    $this->splitOrder($delivery, $storage);
                }
            } catch (OrderSplitException $e) {
                // проверяется на этапе валидации $storage
            }

            if($storage->getSubscribeId() > 0){ // для выбора дня первой доставки
                try {
                    $this->arResult['ORDER_SUBSCRIBE'] = $this->getOrderSubscribeService()->getById($storage->getSubscribeId());
                } catch (\Exception $e) {

                }
            }

            $this->arResult['PICKUP'] = $pickup;
            $this->arResult['DELIVERY'] = $delivery;
            $this->arResult['DELIVERY_DOSTAVISTA'] = $deliveryDostavista;
            $this->arResult['EXPRESS_DELIVERY'] = $expressDelivery;
            $this->arResult['DELIVERY_DOBROLAP'] = $deliveryDobrolap;

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
                        'user' => $user->getId(),
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

            // бонусы
            if ($user) {
                $this->arResult['MAX_BONUS_SUM'] = $this->basketService->getMaxBonusesForPayment($basket); // Получение из Manzana максимального количества бонусов для списания

                /** @var BonusService $bonusService */
                $bonusService = Application::getInstance()->getContainer()->get('bonus.service');
                if ((!isset($bonus) || $bonus->isEmpty()) && !$bonusService->isUserBonusInfoUpdated()) {
                    $bonus = $bonusService->updateUserBonusInfo();
                }
                if (isset($bonus))
                {
                    $maxTemporaryBonuses = $bonus->getTemporaryBonus();
                } else {
                    $maxTemporaryBonuses = $user->getTemporaryBonus();
                }

                $maxTemporaryBonuses = min($maxTemporaryBonuses, $this->arResult['MAX_BONUS_SUM']);
                if (isset($maxTemporaryBonuses) && $maxTemporaryBonuses > 0) {
                    $this->arResult['MAX_TEMPORARY_BONUS_SUM'] = floor($maxTemporaryBonuses);
                }
            }

            // киоск: скидочная карта
            if (KioskService::isKioskMode()) {
                $curPage = BitrixApplication::getInstance()->getContext()->getRequest()->getRequestUri();
                $url = $this->kioskService->addParamsToUrl($curPage, ['bindcard' => true]);
                $this->arResult['BIND_CARD_URL'] = $url;
                $this->arResult['IS_BIND_CARD_URL'] = ($url == $curPage);
                $this->arResult['KIOSK'] = true;
                if ($this->kioskService->getCardNumber()) {
                    $storage->setDiscountCardNumber($this->kioskService->getCardNumber());
                    $this->orderStorageService->updateStorage($storage, OrderStorageEnum::NOVALIDATE_STEP);
                }
            }

            // магнит добролап
            if ($user) {
                $this->checkAndReplaceDobrolapMagnet($basket, $user, $selectedDelivery);
            }

            $payments = $this->orderStorageService->getAvailablePayments($storage, true, true, $basket->getPrice());
        }

        $storageBonus = $storage->getBonus();
        if ($storageBonus) {
            $basketPrice = $basket->getOrderableItems()->getPrice();
            $allowBonusCnt = floor($basketPrice * 0.9);

            if ($storageBonus > $allowBonusCnt) {
                $storage->setBonus($allowBonusCnt);
                $this->orderStorageService->updateStorage($storage, OrderStorageEnum::NOVALIDATE_STEP);
            }
        }

        $this->arResult['BASKET']             = $basket;
        $this->arResult['USER']               = $user;
        $this->arResult['PAYMENTS']           = $payments;
        $this->arResult['SELECTED_CITY']      = $selectedCity;
        $this->arResult['DADATA_CONSTRAINTS'] = $this->locationService->getDadataJsonFromLocationArray($selectedCity);

        $this->arResult['METRO']   = $this->storeService->getMetroInfo();
        $this->arResult['STORAGE'] = $storage;
        $this->arResult['STEP']    = $this->currentStep;
    }

    /**
     * @param CalculationResultInterface[] $deliveries
     * @param OrderStorage                 $storage
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotFoundException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws StoreNotFoundException
     * @throws SystemException
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
            $storage->setDeliveryId($pickup->getDeliveryId());
            $storage->setDeliveryPlaceCode($pickup->getSelectedShop()->getXmlId());

            $this->arResult['PICKUP_AVAILABLE_PAYMENTS'] = $this->orderStorageService->getAvailablePayments($storage, false, true, $pickup->getStockResult()
                ->getPrice());

            if (!$storage->isSubscribe()) {
                $storage->setSplit(true);
                $splitStockResult = $this->orderSplitService->splitStockResult($pickup);
                $available = $splitStockResult->getAvailable();
                $delayed = $splitStockResult->getDelayed();

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
            }
        }
    }

    /**
     * @param CalculationResultInterface[] $deliveries
     * @param OrderStorage                 $storage
     *
     * @param array                        $selectedCity
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotFoundException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws StoreNotFoundException
     * @throws SystemException
     */
    protected function getDobrolapData(array $deliveries, OrderStorage $storage, array $selectedCity): void
    {
        $dobrolap = null;
        foreach ($deliveries as $calculationResult) {
            if ($this->deliveryService->isDobrolapDelivery($calculationResult)) {
                $dobrolap = $calculationResult;
                break;
            }
        }

        if (null !== $dobrolap) {
            /** @var DobrolapDeliveryResult $dobrolap */
            $storage = clone $storage;
            $selectedShopCode = $storage->getDeliveryPlaceCode();
            $shops = $dobrolap->getStockResult()->getStores();

            if ($selectedShopCode && isset($shops[$selectedShopCode])) {
                $dobrolap->setSelectedShop($shops[$selectedShopCode]);
            }

            $this->arResult['DOBROLAP_SELECTED_SHOP'] = $dobrolap->getSelectedShop();

            $storage->setDeliveryId($dobrolap->getDeliveryId());
            $storage->setDeliveryPlaceCode($dobrolap->getSelectedShop()->getXmlId());

            $payments = $this->orderStorageService->getAvailablePayments($storage, false, true, $dobrolap->getStockResult()->getPrice());
            foreach ($payments as $key => $payment) {
                if ($payment['CODE'] != OrderPayment::PAYMENT_ONLINE) {
                    unset($payments[$key]);
                }
            }

            $this->arResult['DOBROLAP_AVAILABLE_PAYMENTS'] = $payments;
            $this->arResult['DOBROLAP_SPLIT_AVAILABLE'] = $this->orderSplitService->canSplitOrder($dobrolap);

            if (!$storage->isSubscribe()) {
                $storage->setSplit(true);
                $splitStockResult = $this->orderSplitService->splitStockResult($dobrolap);
                $available = $splitStockResult->getAvailable();
                $delayed = $splitStockResult->getDelayed();

                $canGetPartial = $this->orderSplitService->canGetPartial($dobrolap);

                if ($canGetPartial) {
                    $available = $this->orderSplitService->recalculateStockResult($available);
                }

                $this->arResult['DOBROLAP_PARTIAL'] = $available->isEmpty()
                    ? null
                    : (clone $dobrolap)->setStockResult($available);

                $this->arResult['DOBROLAP_PARTIAL_AVAILABLE'] = $canGetPartial;
                $this->arResult['DOBROLAP_SPLIT_AVAILABLE'] = $this->orderSplitService->canSplitOrder($dobrolap);
                $this->arResult['DOBROLAP_STOCKS_AVAILABLE'] = $available;
                $this->arResult['DOBROLAP_STOCKS_DELAYED'] = $delayed;
            }
            $shelters = AnimalShelterTable::getList([
                'order' => [
                    'name' => 'asc'
                ]
            ])->fetchAll();

            $checkedShelter = $storage->getShelter();
            $currentShelters = [];
            $currentSheltersMO = [];
            foreach ($shelters as $key => &$shelter) {
                if($shelter['id'] == $checkedShelter){
                    $shelter['checked'] = true;
                }
                if ($selectedCity['NAME'] == $shelter['city']) {
                    $currentShelters[] = $shelter;
                    unset($shelters[$key]);
                } elseif(strpos($shelter['city'], $selectedCity['NAME']) !== false) {
                    $currentShelters[] = $shelter;
                    unset($shelters[$key]);
                } elseif ($selectedCity['NAME'] == 'Москва' && strpos($shelter['city'], 'Московская область') !== false) {
                    $currentSheltersMO[] = $shelter;
                    unset($shelters[$key]);
                }
            }
            $shelters = array_merge($currentShelters, $currentSheltersMO, $shelters);

            $this->arResult['SHELTERS'] = $shelters;
        }
    }

    /**
     * @param CalculationResultInterface $delivery
     * @param OrderStorage               $storage
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
     *
     * @return array
     */
    public function getOrderItemData(StockResultCollection $stockResultCollection): array
    {
        $itemData    = [];
        $totalWeight = 0;
        /** @var StockResult $item */
        foreach ($stockResultCollection->getIterator() as $item) {
            $weight                         = $item->getOffer()->getCatalogProduct()->getWeight() * $item->getAmount();
            $offerId                        = $item->getOffer()->getId();
            $itemData[$offerId]['name']     = $item->getOffer()->getName();
            $itemData[$offerId]['quantity'] += $item->getAmount();
            $itemData[$offerId]['price']    += $item->getPrice();
            $itemData[$offerId]['weight']   += $weight;
            $itemData[$offerId]['brand']    = $item->getOffer()->getProduct()->getBrandName();

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
                    $itemData[$offerId]['name']     = $item->getField('NAME');
                    $itemData[$offerId]['quantity'] += $item->getQuantity();
                    $itemData[$offerId]['price']    += $item->getPrice();
                    $itemData[$offerId]['weight']   += $offer->getCatalogProduct()->getWeight() * $item->getQuantity();
                    break;
                }
            }
        }

        return $itemData;
    }

    /**
     * @return ShopInfoService
     */
    public function getShopListService(): ShopInfoService
    {
        return $this->shopListService;
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
        $script   = '';
        $option   = '';
        $step     = 1;
        $isPreset = true;

        switch ($this->currentStep) {
            case OrderStorageEnum::COMPLETE_STEP:
                return $script;
            case OrderStorageEnum::AUTH_STEP:
                $step     = 2;
                $option   = 'Контактные данные';
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

    /**
     * @return OrderSubscribeService|object
     */
    public function getOrderSubscribeService()
    {
        if (!$this->orderSubscribeService) {
            $this->orderSubscribeService = Application::getInstance()->getContainer()->get(
                'order_subscribe.service'
            );
        }
        return $this->orderSubscribeService;
    }

    /**
     * @param Basket $basket
     * @param User $user
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws SystemException
     * @throws BitrixProxyException
     */
    private function checkAndReplaceDobrolapMagnet(Basket $basket, User $user, CalculationResultInterface $selectedDelivery)
    {
        //return; // Отключены лишние запросы для проверки магнитиков
        $magnets = $this->basketService->getDobrolapMagnets();
        if(!$magnets){
            return;
        }
        $magnetIds = array_column($magnets, 'ID');
        /** @var BasketItem $basketItem */
        foreach($basket as $basketItem) {
            if(in_array($basketItem->getProductId(), $magnetIds)){
                if($basketItem->getProductId() == $magnets[BasketService::GIFT_DOBROLAP_XML_ID]['ID']
                    && ($this->deliveryService->isInnerPickup($selectedDelivery) || $this->deliveryService->isDostavistaDelivery($selectedDelivery))
                ){
                    $this->basketService->deleteOfferFromBasket($basketItem->getId());
                    try {
                        $this->basketService->addOfferToBasket(
                            (int)$magnets[BasketService::GIFT_DOBROLAP_XML_ID_ALT]['ID'],
                            1,
                            [],
                            true,
                            $basket
                        );
                    } catch (\Exception $e) {
                        $this->logger->error(sprintf('Не удалось добавить альтерантивынй магнит в корзину: %s', $e->getMessage()), [
                            'user' => $user->getId(),
                        ]);
                    }
                }
                if($basketItem->getProductId() == $magnets[BasketService::GIFT_DOBROLAP_XML_ID_ALT]['ID']
                    && (!$this->deliveryService->isInnerPickup($selectedDelivery) && !$this->deliveryService->isDostavistaDelivery($selectedDelivery))
                ){
                    $this->basketService->deleteOfferFromBasket($basketItem->getId());
                    try {
                        $this->basketService->addOfferToBasket(
                            (int)$magnets[BasketService::GIFT_DOBROLAP_XML_ID_ALT]['ID'],
                            1,
                            [],
                            true,
                            $basket
                        );
                    } catch (\Exception $e) {
                        $this->logger->error(sprintf('Не удалось добавить альтерантивынй магнит в корзину: %s', $e->getMessage()), [
                            'user' => $user->getId(),
                        ]);
                    }
                }
            }
        }
    }
}
