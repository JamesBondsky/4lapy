<?php

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Entity\UserFieldEnumValue;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Entity\OrderSubscribeItem;
use FourPaws\PersonalBundle\Entity\OrderSubscribeSingle;
use FourPaws\PersonalBundle\Repository\OrderSubscribeSingleRepository;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\PersonalBundle\Service\OrderSubscribeHistoryService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Service\PiggyBankService;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\ShopInfoService;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use Bitrix\Main\Application as BitrixApplication;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsPersonalCabinetOrdersSubscribeFormComponent extends CBitrixComponent
{
    use LazyLoggerAwareTrait;


    /** @var string $action */
    private $action = '';

    /** @var UserService $userCurrentUserService */
    private $userCurrentUserService;

    /** @var OrderSubscribeService $orderSubscribeService */
    private $orderSubscribeService;

    /** @var OrderSubscribeHistoryService $orderSubscribeHistoryService */
    private $orderSubscribeHistoryService;

    /** @var OrderService $orderService */
    private $orderService;

    /** @var DeliveryService $deliveryService */
    private $deliveryService;

    /** @var StoreService $storeService */
    private $storeService;

    /** @var ShopInfoService $shopListService */
    private $shopListService;

    /** @var BasketService $basketService */
    private $basketService;

    /** @var LocationService $locationService */
    private $locationService;

    /** @var array $data */
    private $data = [];

    /** @var array $offers */
    private $offers = [];

    /** @var array $offers */
    private $images = [];

    /** @var Basket $basket */
    private $basket;

    /** @var array $deliveries */
    private $deliveries;

    /** @var OrderSubscribe $subscribe */
    private $subscribe;

    /** @var array $items */
    private $items;

    /** @var \Bitrix\Sale\PaySystem\Service $payment */
    private $payment;

    /** @var PaymentCollection $paymentCollection */
    private $paymentCollection;

    /** @var array */
    protected $fieldCaptions = [
        'dateStart' => 'Дата первой доставки',
        'deliveryFrequency' => 'Как часто',
        'deliveryInterval' => 'Интервал',
    ];

    /** @var bool */
    private $changeNextDelivery;

    /** @var OrderSubscribeSingleRepository $orderSubscribeSingleRepository */
    private $orderSubscribeSingleRepository;

    /** @var ArrayTransformerInterface $arrayTransformer */
    private $arrayTransformer;

    /** @var OrderSubscribeSingle */
    private $orderSubscribeSingle;


    /**
     * FourPawsPersonalCabinetOrdersSubscribeFormComponent constructor.
     *
     * @param null|\CBitrixComponent $component
     */
    public function __construct($component = null)
    {
        // LazyLoggerAwareTrait не умеет присваивать имя по классам без неймспейса
        // делаем это вручную
        $this->logName = __CLASS__;

        $this->orderSubscribeSingleRepository = Application::getInstance()->getContainer()->get('order_subscribe_single.repository');
        $this->arrayTransformer = Application::getInstance()->getContainer()->get(SerializerInterface::class);

        parent::__construct($component);
    }

    /**
     * @param $params
     * @return array
     * @throws ApplicationCreateException
     */
    public function onPrepareComponentParams($params)
    {
        $this->arResult['ORIGINAL_PARAMETERS'] = $params;

        $params['CACHE_TYPE'] = $params['CACHE_TYPE'] ?? 'A';
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 3600;

        $params['ORDER_ID'] = $params['ORDER_ID'] ? (int)$params['ORDER_ID'] : 0;
        try {
            $params['USER_ID'] = $this->getUserService()->getCurrentUserId();
        } catch (\Exception $exception) {
            $params['USER_ID'] = 0;
        }

        $params['INCLUDE_TEMPLATE'] = $params['INCLUDE_TEMPLATE'] ?? 'Y';
        $params = parent::onPrepareComponentParams($params);

        return $params;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function executeComponent()
    {
        try {
            if($this->arParams['GET_DELIVERY_DATES']){
                $this->arResult['DATES'] = $this->getDeliveryDates($this->arParams['STORE_ID'], $this->arParams['ITEMS']);
            } else{
                $this->setAction($this->prepareAction());
                $this->doAction();
            }
        } catch (\Exception $exception) {
            $this->log()->critical(
                sprintf(
                    '%s exception: %s',
                    __FUNCTION__,
                    $exception->getMessage()
                ),
                $this->arParams
            );
            throw $exception;
        }

        return $this;
    }

    /**
     * @param $storeXmlId
     * @param $items
     * @return array
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Sale\UserMessageException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws Exception
     */
    public function getDeliveryDates($storeXmlId, $items)
    {
        $dates = [];
        $isOrder = false;
        $basketService = $this->getBasketService();
        $deliveryService = $this->getDeliveryService();
        $storeService = $this->getStoreService();

        // запрос может прийти без товаров из оформления заказов
        // в таком случае value это id-шник службы доставки из массива getNextDeliveries
        if(!empty($items)){
            $basket = $basketService->createBasketFromItems($items);
        } else {
            $basket = $basketService->getBasket();
            $isOrder = true;
        }

        $deliveries = $deliveryService->getByBasket($basket, '', [DeliveryService::INNER_PICKUP_CODE, DeliveryService::DPD_PICKUP_CODE]);
        if(empty($deliveries)){
            throw new Exception("Не удалось сформировать службы доставки");
        }

        /** @var PickupResult $pickup */
        $pickup = current($deliveries);

        if ($deliveryService->isPickup($pickup)) {
            try {
                $store = $storeService->getStoreByXmlId($storeXmlId);
            } catch (\Exception $e) {
                // если склад не найден - попробуем найти его в DPD
                $store = $deliveryService->getDpdTerminalByCode($storeXmlId);
                if (!$store) {
                    throw new \FourPaws\PersonalBundle\Exception\NotFoundException(sprintf("Склад с XML_ID=%s не найден в DPD", $storeXmlId));
                }
            }
            $pickup->setSelectedStore($store);
        }

        $nextDeliveries = $deliveryService->getNextDeliveries($pickup, 10);
        foreach($nextDeliveries as $i => $delivery){
            $tmpPickup = clone $delivery;
            $dates[] = [
                'value' => ($isOrder) ? $i : $tmpPickup->getDeliveryDate()->format('d.m.Y'),
                'name' => FormatDate('l, d.m.Y', $delivery->getDeliveryDate()->getTimestamp()),
                'date' => FormatDate('l, Y-m-d', $delivery->getDeliveryDate()->getTimestamp()),
            ];
        }

        return $dates;
    }

    /**
     * @return UserService
     * @throws ApplicationCreateException
     */
    public function getUserService()
    {
        if (!$this->userCurrentUserService) {
            $appCont = Application::getInstance()->getContainer();
            $this->userCurrentUserService = $appCont->get(CurrentUserProviderInterface::class);
        }

        return $this->userCurrentUserService;
    }

    /**
     * @return ShopInfoService
     */
    public function getShopListService(): ShopInfoService
    {
        if (!$this->shopListService) {
            $appCont = Application::getInstance()->getContainer();
            $this->shopListService = $appCont->get(ShopInfoService::class);
        }
        return $this->shopListService;
    }

    /**
     * @return BasketService
     */
    public function getBasketService(): BasketService
    {
        if (!$this->basketService) {
            $appCont = Application::getInstance()->getContainer();
            $this->basketService = $appCont->get(BasketService::class);
        }
        return $this->basketService;
    }

    /**
     * @return OrderService
     */
    public function getOrderService(): OrderService
    {
        if (!$this->orderService) {
            $appCont = Application::getInstance()->getContainer();
            $this->orderService = $appCont->get(OrderService::class);
        }
        return $this->orderService;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return LocationService
     */
    public function getLocationService(): LocationService
    {
        if (!$this->locationService) {
            $appCont = Application::getInstance()->getContainer();
            $this->locationService = $appCont->get('location.service');
        }
        return $this->locationService;
    }

    /**
     * @return StoreService
     */
    public function getStoreService(): StoreService
    {
        if (!$this->storeService) {
            $appCont = Application::getInstance()->getContainer();
            $this->storeService = $appCont->get('store.service');
        }
        return $this->storeService;
    }

    /**
     * @return OrderSubscribe
     */
    public function getSubscribe(): ?OrderSubscribe
    {
        return $this->subscribe;
    }

    /**
     * @param OrderSubscribe $subscribe
     */
    public function setSubscribe(OrderSubscribe $subscribe)
    {
        $this->subscribe = $subscribe;
        return $this->subscribe;
    }

    /**
     * @return DeliveryService
     * @throws ApplicationCreateException
     */
    public function getDeliveryService()
    {
        if (!$this->deliveryService) {
            $appCont = Application::getInstance()->getContainer();
            $this->deliveryService = $appCont->get('delivery.service');
        }

        return $this->deliveryService;
    }

    /**
     * @return UserRepository
     * @throws ApplicationCreateException
     */
    public function getUserRepository()
    {
        return $this->getUserService()->getUserRepository();
    }

    /**
     * @param BasketBase $basket
     * @return BasketBase
     */
    public function setBasket(Basket $basket): BasketBase
    {
        $this->basket = $basket;
        return $basket;
    }

    /**
     * @return BasketBase
     */
    public function getBasket(): BasketBase
    {
        return $this->basket;
    }

    /**
     * @return OrderSubscribeService
     * @throws ApplicationCreateException
     */
    public function getOrderSubscribeService()
    {
        if (!$this->orderSubscribeService) {
            $appCont = Application::getInstance()->getContainer();
            $this->orderSubscribeService = $appCont->get('order_subscribe.service');
        }

        return $this->orderSubscribeService;
    }

    /**
     * @return OrderSubscribeHistoryService
     * @throws ApplicationCreateException
     */
    public function getOrderSubscribeHistoryService()
    {
        if (!$this->orderSubscribeHistoryService) {
            $appCont = Application::getInstance()->getContainer();
            $this->orderSubscribeHistoryService = $appCont->get('order_subscribe_history.service');
        }

        return $this->orderSubscribeHistoryService;
    }

    /**
     * @param string $action
     * @return void
     */
    protected function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    protected function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    protected function getActionReal()
    {
        return $this->request->get('action');
    }

    /**
     * @return string
     */
    protected function prepareAction()
    {
        $action = 'initialLoad';

        switch ($this->request->get('action')) {
            case 'deliveryOrderSubscribe':
            case 'renewalSubmit':
                $action = 'subscribe';
                break;
            case 'deliveryOrderUnsubscribe':
                $action = 'unsubscribe';
                break;
            case 'renewal':
                $action = 'getRenewal';
                break;
            case 'item':
                $action = 'getItem';
                break;
        }

        return $action;
    }

    protected function doAction()
    {
        $action = $this->getAction();
        if (is_callable(array($this, $action.'Action'))) {
            call_user_func(array($this, $action.'Action'));
        }
    }

    /**
     * Форма редактирования + контролы
     *
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     */
    protected function initialLoadAction()
    {
        $this->loadData();
    }

    /**
     * Форма возобновления подписки
     *
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     */
    protected function getRenewalAction()
    {
        $this->loadData();
    }

    /**
     * Возвращает HTML товара
     */
    protected function getItemAction()
    {
        try {
            $offerId = (int)$this->request->get('productId');
            $items = [
                0 => [
                    'productId' => $offerId,
                    'quantity' => $this->request->get('quantity')
                ]
            ];

            $offer = $this->getOffer($offerId);
            if(!$offer){
                throw new \FourPaws\PersonalBundle\Exception\NotFoundException(sprintf("Товар не найден: %s", $offerId));
            }

            // всегда сбрасываем кеш для отрабатывания шаблона
            $this->abortResultCache();

            $basket = $this->createBasketFromItems($items);
            $this->setBasket($basket);

            $this->arResult['CURRENT_STAGE'] = 'item';
        } catch (Exception $e) {
            $this->arResult['CURRENT_STAGE'] = 'error';
            $this->arResult['ERROR'][]= $e->getMessage();
        }

        $this->includeComponentTemplate();
    }

    /**
     * Метод обновляет подписку на доставку.
     * Создание подписки на доставку сейчас не используется.
     * Если юзер выбирает редактировать всю подписку
     * и у него есть единичная доставка (!$isSingleSubscribe && $orderSubscribeSingle),
     * то обновляется "слепок" подписки из OrderSubscribeSingleRepository.
     *
     * @throws ApplicationCreateException
     * @throws Exception
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    protected function subscribeAction()
    {
        $this->initPostFields();
        if ($this->arResult['FIELD_VALUES']['orderId']) {
            $this->arParams['ORDER_ID'] = (int)$this->arResult['FIELD_VALUES']['orderId'];
        }

        $this->arResult['SUBSCRIBE_ACTION']['SUCCESS'] = 'N';
        $this->processSubscribeFormFields();

        if (empty($this->arResult['ERROR']['FIELD'])) {
            /** @var OrderSubscribeService $orderSubscribeService */
            $orderSubscribeService = $this->getOrderSubscribeService();

            // редактирование всей подписки при наличии единичной доставки
            if($this->isSingleSubscribeMode()){
                $arOrderSubscribe = $this->getSingleSubscribe()->getSubscribe();
                $orderSubscribe = $this->arrayTransformer->fromArray($arOrderSubscribe, OrderSubscribe::class);
            } else {
                $orderSubscribe = $this->getOrderSubscribe(true);
            }

            $order = $this->getOrder();
            if(!$order && !$orderSubscribe){
                $this->setExecError('subscribeAction', "Необходимо наличие заказа или подписки для действия", 'subscriptionUpdate');
            }

            if (empty($this->arResult['ERROR']['EXEC'])) {
                if (!$orderSubscribe) {
                    $orderSubscribe = (new OrderSubscribe());
                } else {
                    $orderIdsForDelete = $this->getOrderSubscribeHistoryService()->getNotDeliveredOrderIds($orderSubscribe);
                }

                $orderSubscribe->setOrderId($order->getId())
                    ->setLastCheck(null);

                $deliveryDate = new DateTime($this->arResult['FIELD_VALUES']['deliveryDate']);
                $orderSubscribe->setNextDate($deliveryDate)
                    ->setCheckDays(new \DateTime($deliveryDate->toString()))
                    ->countDateCheck();

                $frequency = $this->arResult['FIELD_VALUES']['subscribeFrequency'];
                if($frequency){
                    $orderSubscribe->setFrequency($frequency);
                }

                if(!empty($this->arResult['FIELD_VALUES']['deliveryInterval'])){
                    $orderSubscribe->setDeliveryTime($this->arResult['FIELD_VALUES']['deliveryInterval']);
                }

                $deliveryId = $this->arResult['FIELD_VALUES']['deliveryId'];
                if($deliveryId){
                    $deliveryService = $this->getDeliveryService();
                    $deliveryCode = $deliveryService->getDeliveryCodeById($deliveryId);

                    // место доставки
                    if($deliveryService->isDeliveryCode($deliveryCode)){
                        $locationService = $this->getLocationService();
                        $userService = $this->getUserService();

                        $personalAddress = (new Address())->setCity($userService->getSelectedCity()['NAME'])
                            ->setLocation($locationService->getCurrentLocation())
                            ->setStreet($this->arResult['FIELD_VALUES']['street'])
                            ->setHouse($this->arResult['FIELD_VALUES']['house'])
                            ->setHousing($this->arResult['FIELD_VALUES']['building'])
                            ->setEntrance($this->arResult['FIELD_VALUES']['porch'])
                            ->setFloor($this->arResult['FIELD_VALUES']['floor'])
                            ->setFlat($this->arResult['FIELD_VALUES']['apartment'])
                            ->setUserId($order->getUserId());

                        try {
                            //$addressService->add($personalAddress);
                            $deliveryPlace = $personalAddress->getFullAddress();
                        } catch (\Exception $e) {
                            $this->log()->error(sprintf('failed to save address: %s', $e->getMessage()), [
                                'city' => $personalAddress->getCity(),
                                'location' => $personalAddress->getLocation(),
                                'userId' => $personalAddress->getUserId(),
                                'street' => $personalAddress->getStreet(),
                                'house' => $personalAddress->getHouse(),
                                'housing' => $personalAddress->getHousing(),
                                'entrance' => $personalAddress->getEntrance(),
                                'floor' => $personalAddress->getFloor(),
                                'flat' => $personalAddress->getFlat(),
                            ]);

                            $this->setExecError('personalAddress', 'Не удалось сохранить новый адрес');
                        }
                    } else {
                        $deliveryPlace = $this->arResult['FIELD_VALUES']['shopId'];
                    }

                    $orderSubscribe->setDeliveryId($deliveryId)
                        ->setDeliveryPlace($deliveryPlace)
                        ->setLocationId($this->getLocationService()->getCurrentLocation());
                }

                $orderSubscribe->setPayWithbonus($this->arResult['FIELD_VALUES']['subscribeBonus'] ? true : false);

                if($this->isRenewalAction()){
                    $orderSubscribe->setActive(true);
                    $this->arResult['SUBSCRIBE_ACTION']['RESUMED'] = 'Y';
                }

                BitrixApplication::getConnection()->startTransaction();

                // обновление подписки
                if ($orderSubscribe->getId() > 0) {
                    $this->arResult['SUBSCRIBE_ACTION']['SUBSCRIPTION_ID'] = $orderSubscribe->getId();
                    $this->arResult['SUBSCRIBE_ACTION']['TYPE'] = 'UPDATE';

                    if($this->isSingleSubscribeMode()) {
                        // в данном случае подписка обновляется ниже, вместе с товарами
                    }
                    else {
                        try {
                            $updateResult = $orderSubscribeService->update($orderSubscribe);

                            if ($updateResult->isSuccess()) {
                                $this->arResult['SUBSCRIBE_ACTION']['SUCCESS'] = 'Y';

                                /** @var OrderSubscribeSingle $singleSubscribeInactive */
                                $singleSubscribeInactive = $this->orderSubscribeSingleRepository->findBy([
                                        'filter' => [
                                            '=UF_SUBSCRIBE_ID' => $orderSubscribe->getId(),
                                            '=UF_ACTIVE' => 0
                                        ]
                                    ])->first();
                                if($singleSubscribeInactive){
                                    $singleSubscribeInactive->setActive(1);
                                    $result = $this->orderSubscribeSingleRepository->setEntity($singleSubscribeInactive)->update();
                                    if(!$result){
                                        $this->setExecError(
                                            'subscribeAction',
                                            'Не удалось активировать единичную доставку',
                                            'subscriptionSingleUpdate'
                                        );
                                    }
                                }

                                // при успешном обновлении нам нужно удалить ранее созданные,
                                // но не доставленные заказы по подписке
                                if (count($orderIdsForDelete) > 0) {
                                    foreach ($orderIdsForDelete as $orderIdForDelete) {
                                        $orderService = $this->getOrderService();
                                        $order = $orderService->getOrderById($orderIdForDelete);
                                        $order->setField('CANCELED', 'Y');
                                        $order->save();
                                    }
                                }

                                if ($this->getActionReal() != 'renewalSubmit') {
                                    $orderSubscribeService->deleteAllItems($orderSubscribe->getId());
                                }

                                $this->flushOrderSubscribe();
                                $this->clearTaggedCache();
                            } else {
                                $this->setExecError('subscribeAction', $updateResult->getErrors(), 'subscriptionUpdate');
                            }

                        } catch (\Exception $exception) {
                            $this->setExecError('subscribeAction', $exception->getMessage(), 'subscriptionUpdateException');
                        }
                    }
                } else {
                    $this->setExecError('subscribeAction', "Не определена подписка на доставку", 'subscriptionUpdate');

                    // не используется
//                    $orderSubscribe->setActive(true);
//                    $this->arResult['SUBSCRIBE_ACTION']['TYPE'] = 'CREATE';
//                    $addResult = $orderSubscribeService->add($orderSubscribe);
//                    if ($addResult->isSuccess()) {
//                        $this->arResult['SUBSCRIBE_ACTION']['SUCCESS'] = 'Y';
//                        $this->arResult['SUBSCRIBE_ACTION']['SUBSCRIPTION_ID'] = $orderSubscribe->getId();
//
//                        $this->flushOrderSubscribe();
//                        $this->clearTaggedCache();
//                    } else {
//                        $this->setExecError('subscribeAction', $addResult->getErrors(), 'subscriptionAdd');
//                    }
                }

                // привязка товаров
                if(empty($this->arResult['ERROR'])){
                    $items = $this->request->get('items');

                    if(empty($items) && $this->getActionReal() != 'renewalSubmit'){
                        $this->setExecError('subscribeAction', "Не переданы товары для подписки", 'subscriptionAdd');
                    }

                    if(!empty($items)){
                        if($this->isSingleSubscribeMode()){
                            $subscribeItems = [];
                            foreach($items as $item){
                                $subscribeItems[$item['productId']] = $item['quantity'];
                            }

                            $arOrderSubscribe = $this->arrayTransformer->toArray($orderSubscribe);
                            $orderSubscribeSingle = $this->getSingleSubscribe()
                                ->setData(serialize($arOrderSubscribe))
                                ->setItems(serialize($subscribeItems))
                                ->setDateCreate(new DateTime());

                            if($orderSubscribeService->getOrderSubscribeSingleRepository()->setEntity($orderSubscribeSingle)->update()){
                                $this->arResult['SUBSCRIBE_ACTION']['SUCCESS'] = 'Y';
                            } else {
                                $this->setExecError('subscribeAction', sprintf("Не удалось обновить оригинальную подписку %s", $orderSubscribeSingle->getId()), 'subscriptionSingleEdit');
                            }
                        } else {
                            foreach($items as $item){
                                $subscribeItem = (new OrderSubscribeItem())
                                    ->setOfferId($item['productId'])
                                    ->setQuantity($item['quantity']);

                                if(!$orderSubscribeService->addSubscribeItem($orderSubscribe, $subscribeItem)){
                                    $this->setExecError('subscribeAction', sprintf("Не удалось добавить товар %s", $subscribeItem->getOfferId()), 'subscriptionAdd');
                                }
                            }
                        }
                    }

                    // создание нового заказа по подписке
                    if($orderSubscribe->isActive()){

                        // убрали по требованию заказчика
                        // if($this->arResult['SUBSCRIBE_ACTION']['TYPE'] == 'UPDATE' && $this->arResult['SUBSCRIBE_ACTION']['SUCCESS'] == 'Y'){
                        //     $result = $orderSubscribeService->processOrderSubscribe($orderSubscribe);
                        //    if(!$result->isSuccess()){
                        //         throw new Exception(sprintf('Не удалось создать заказ по новой подписке: %s', implode("; ", $result->getErrorMessages())));
                        //     }
                        // }

                        // отправка уведомления о возобновленной или отредактированной подписке
                        $this->sendOrderSubscribedNotification($orderSubscribe->getId());
                    }
                }

                if (empty($this->arResult['ERROR'])) {
                    BitrixApplication::getConnection()->commitTransaction();
                    $subscribeId = $orderSubscribe->getId();
                    if ($subscribeId >= 0) {
                        $producer = Application::getInstance()->getContainer()->get('old_sound_rabbit_mq.order_subscription_creating_producer');
                        $producer->publish($subscribeId);
                    }
                } else{
                    BitrixApplication::getConnection()->rollbackTransaction();
                    $this->log()->error(__METHOD__.' ошибка выполнения: '.$this->getExecErrors());
                }
            } else {
                $this->log()->error(__METHOD__.' ошибка выполнения: '.$this->getExecErrors());
            }
        } else {
            $this->log()->error(__METHOD__.' ошибка валидации: '.$this->getFieldErrors());
        }

        $this->loadData();
    }

    /**
     * @throws ApplicationCreateException
     * @throws Exception
     */
    protected function unsubscribeAction()
    {
        if ($this->request->get('orderId')) {
            $this->arParams['ORDER_ID'] = (int)$this->request->get('orderId');
        }

        $this->arResult['UNSUBSCRIBE_ACTION']['SUCCESS'] = 'N';

        $orderSubscribe = $this->getOrderSubscribeService()->getById((int)$this->request->get('subscribeId'));
        if ($orderSubscribe) {
            $this->arResult['UNSUBSCRIBE_ACTION']['SUBSCRIPTION_ID'] = $orderSubscribe->getId();
            // не удаляем запись, а деактивируем
            try {
                $updateResult = $this->getOrderSubscribeService()->deactivateSubscription($orderSubscribe);
                if ($updateResult->isSuccess()) {
                    $this->arResult['UNSUBSCRIBE_ACTION']['SUCCESS'] = 'Y';
                    $this->flushOrderSubscribe();
                    $this->clearTaggedCache();
                } else {
                    $this->setExecError('unsubscribeAction', $updateResult->getErrors(), 'subscriptionUpdate');
                }
            } catch (\Exception $exception) {
                $this->setExecError('subscribeAction', $exception->getMessage(), 'subscriptionUpdateException');
            }
        }

        $this->loadData();
    }

    /**
     * Отправка уведомления о созданной подписке
     *
     * @param int $subscribeId
     * @throws ApplicationCreateException
     */
    protected function sendOrderSubscribedNotification(int $subscribeId)
    {
        $orderSubscribeService = $this->getOrderSubscribeService();
        $orderSubscribe = $orderSubscribeService->getSubscribeById($subscribeId);
        if ($orderSubscribe) {
            $orderSubscribeService->sendOrderSubscribedNotification($orderSubscribe);
        }
    }

    /**
     * @throws ApplicationCreateException
     * @throws Exception
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     */
    protected function loadData()
    {
        if ($this->getAction() === 'initialLoad') {
            // в первом случае получаем контролы для списка заказов
            // иначе форму редактирования подписки
            if(null === $this->arParams['STEP']){
                $this->arResult['ORDER'] = $this->getOrder();

                $this->arResult['ORDER_SUBSCRIBE'] = $this->getOrderSubscribe(false);
                $this->isBySubscribe();
                $this->getSubscribePrice();

                $this->arResult['CURRENT_STAGE'] = 'initial';
            } else if($this->arParams['STEP'] == 1){
                $this->initStep1();
            } else if ($this->arParams['STEP'] == 2) {
                $this->initStep2();
            }
        } else if ($this->getAction() === 'getRenewal') {
            // расчёты такие же как и на втором шаге, но выводим только часть формы
            $result = $this->initStep2();
            $this->arResult['CURRENT_STAGE'] = $result->isSuccess() ? 'renewal' : 'error';
        }

        if ($this->arParams['INCLUDE_TEMPLATE'] !== 'N') {
            $this->includeComponentTemplate();
        }
    }

    /**
     * @return Result
     */
    private function initRenewal()
    {
        $result = new Result();
        $subscribeId = $this->request->get('subscribeId');

        if(!($subscribeId > 0)){
            $result->addError(new Error("Необходимо передать id подписки"));
        }

        try {
            $orderSubscribeService = $this->getOrderSubscribeService();
            $orderSubscribe = $orderSubscribeService->getById($subscribeId);
            $this->setSubscribe($orderSubscribe);
        } catch (\Exception $e) {
            $result->addError(new Error(
                sprintf("Error while trying to get ordersubscribe: %s", $e->getMessage()),
                'initRenewal'
            ));
        }

        $this->arResult['CURRENT_STAGE'] = $result->isSuccess() ? 'renewal' : 'error';
        return $result;
    }

    /**
     * @return Result
     * @throws \Bitrix\Main\ArgumentNullException
     */
    private function initStep1(): Result
    {
        $result = new Result();

        if($this->arParams['SUBSCRIBE_ID'] > 0){ // редактирование подписки
            try
            {
                if($orderSubscribeSingle = $this->getOrderSubscribeService()->getSingleSubscribe($this->arParams['SUBSCRIBE_ID'])) {
                    $items = [];
                    foreach ($orderSubscribeSingle->getItems() as $offerId => $quantity){
                        $items[] = [
                            'productId' => $offerId,
                            'quantity' => $quantity,
                        ];
                    }
                    $basket = $this->getBasketService()->createBasketFromItems($items);
                } else {
                    $basket = $this->getOrderSubscribeService()->getBasketBySubscribeId($this->arParams['SUBSCRIBE_ID']);
                }

                $this->arResult['TITLE'] = "Редактирование подписки";
            }
            catch (\Exception $e) {
                $result->addError(new Error(
                    sprintf("Ошибка формирования корзины: %s", $e->getMessage()),
                    'getBasketBySubscribeId',
                    ['id' => $this->arParams['SUBSCRIBE_ID']]
                ));
            }
        } else if($this->arParams['ORDER_ID'] > 0){ // создание подписки
            try {
                $basket = $this->getOrder()->getBitrixOrder()->getBasket();

                /** @var PiggyBankService $piggyBankService */
                $piggyBankService = Application::getInstance()->getContainer()->get('piggy_bank.service');

                /** @var BasketItem $basketItem */
                foreach ($basket as $basketItem){ // исключить виртуальные марки
                    if (in_array($basketItem->getProductId(), $piggyBankService->getMarksIds(), false)){
                        $basket->deleteItem($basketItem->getInternalIndex());
                    }
                }
                $this->arResult['TITLE'] = "Создание подписки";
            } catch (\Exception $e) {
                $result->addError(new Error(
                    sprintf("Ошибка формирования корзины: %s", $e->getMessage()),
                    'getBasket',
                    ['id' => $this->arParams['ORDER_ID']]
                ));
            }
        }

        if(!$result->isSuccess()){
            return $result;
        }

        if(null !== $basket) {
            $offerIds = [];
            /** @var BasketItem $basketItem */
            foreach ($basket as $basketItem) {
                $offerIds[] = $basketItem->getProductId();
            }

            if(empty($offerIds)){
                $result->addError(new Error("Offers is empty"));
            }
        } else {
            $result->addError(new Error("Товары не найдены"));
        }

        if($result->isSuccess()){
            if(!$this->setOffers($offerIds)){
                $result->addError(new Error('Failed to set offers'));
            }
        }

        if(!$result->isSuccess()){
            $this->arResult['CURRENT_STAGE'] = 'error';
            $this->arResult['ERROR'] = $result->getErrorMessages();
        } else {
            $this->arResult['CURRENT_STAGE'] = 'step1';
            $this->arResult['BASKET'] = $this->setBasket($basket);
            $this->arResult['ITEMS'] = $this->getItemsFormatted();
        }

        return $result;
    }

    /**
     * @return Result
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Sale\UserMessageException
     * @throws \FourPaws\AppBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    private function initStep2(): Result
    {
        $result = new Result();
        $isSingleSubscribe = false;

        if($this->arParams['SUBSCRIBE_ID'] > 0) {
            $isSingleSubscribe = $this->request->get('changeNextDelivery');
            $orderSubscribeSingle = $this->getOrderSubscribeService()->getSingleSubscribe($this->arParams['SUBSCRIBE_ID']);

            if(!$isSingleSubscribe && $orderSubscribeSingle){
                $arOrderSubscribe = $orderSubscribeSingle->getSubscribe();
                $orderSubscribe = $this->arrayTransformer->fromArray($arOrderSubscribe, OrderSubscribe::class);
            } else {
                $orderSubscribe = $this->getOrderSubscribeService()->getById($this->arParams['SUBSCRIBE_ID']);
            }

            $this->arResult['SUBSCRIBE'] = $this->setSubscribe($orderSubscribe);

            if($isSingleSubscribe && !$orderSubscribeSingle){
                $isSuccess = $this->getOrderSubscribeService()->createSingleSubscribe($this->getSubscribe());
                if(!$isSuccess){
                    $result->addError(new Error(
                        sprintf("Не удалось создать единичную подписку на доставку: %s", $this->arParams['SUBSCRIBE_ID'])
                    ));
                }
            }
        }

        // товары
        $items = [];
        if(is_array($this->request->get('items')))
        {
            $items = $this->request->get('items');
        }
        else if($this->getOrderSubscribe(true) && $this->getOrderSubscribe()->getId() > 0)
        {
            if(!$isSingleSubscribe && $orderSubscribeSingle){
                foreach($orderSubscribeSingle->getItems() as $offerId => $quantity){
                    $items[] = [
                        'productId' => $offerId,
                        'quantity' => $quantity,
                    ];
                }
            } else {
                $subscribeItems = $this->getOrderSubscribeService()->getItemsBySubscribeId($this->getOrderSubscribe()->getId());

                /** @var OrderSubscribeItem $item */
                foreach($subscribeItems as $item){
                    $items[] = [
                        'productId' => $item->getOfferId(),
                        'quantity' => $item->getQuantity(),
                    ];
                }
            }
        }

        try {
            if(empty($items)){
                throw new \Bitrix\Main\ArgumentException("Items can't be empty");
            }
            $this->setItems($items);
            $basket = $this->createBasketFromItems($this->getItems());
            $this->setBasket($basket);
        } catch (\Exception $e) {
            $result->addError(new Error(
                sprintf("Failed to get basket for form: %s", $e->getMessage())
            ));
        }

        if($result->isSuccess()){
            try{
                $selectedCity     = $this->getUserService()->getSelectedCity();
                $deliveries       = $this->getDeliveries($basket);
                $selectedDelivery = $this->getSelectedDelivery();

                $this->getPickupData($deliveries);

//                $addresses = null;
//                if ($this->getUserService()->getCurrentUserId()) {
//                    /** @var AddressService $addressService */
//                    $addressService = Application::getInstance()->getContainer()->get('address.service');
//                    $addresses      = $addressService->getAddressesByUser($this->getUserService()->getCurrentUserId(), $selectedCity['CODE']);
//                }

                $delivery = null;
                $pickup   = null;
                foreach ($deliveries as $calculationResult) {
                    if ($this->deliveryService->isPickup($calculationResult)) {
                        $pickup = $calculationResult;
                    } elseif ($this->deliveryService->isDelivery($calculationResult)) {
                        $delivery = $calculationResult;
                    }
                }

                if($this->deliveryService->isDelivery($selectedDelivery) && $this->getLocationService()->getCurrentLocation() == $this->getOrderSubscribe()->getLocationId()){
                    $this->arResult['ADDRESS'] = $this->getLocationService()->splitAddress($this->getOrderSubscribe()->getDeliveryPlace());
                }

                $this->arResult['PICKUP']               = $pickup;
                $this->arResult['DELIVERY']             = $delivery;
                //$this->arResult['ADDRESSES']            = $addresses;
                $this->arResult['SELECTED_DELIVERY']    = $selectedDelivery;
                $this->arResult['PICKUP_AVAILABLE_PAYMENTS'] = $this->getAvailablePayments();

                $this->arResult['SELECTED_CITY'] = $selectedCity;
                $this->arResult['DADATA_CONSTRAINTS'] = $this->getLocationService()->getDadataJsonFromLocationArray($selectedCity);
                $this->arResult['METRO'] = $this->getStoreService()->getMetroInfo();
                $this->arResult['IS_SINGLE_SUBSCRIBE'] = $isSingleSubscribe;

                if($orderSubscribeSingle) {
                    $arOrderSubscribe = $orderSubscribeSingle->getSubscribe();
                    $orderSubscribe = $this->arrayTransformer->fromArray($arOrderSubscribe, OrderSubscribe::class);
                }
                $this->arResult['SELECTED_DATE'] = $orderSubscribe->getNearestDelivery();
                $this->arResult['SELECTED_TIME'] = $orderSubscribe->getDeliveryTime();
                $this->arResult['SELECTED_FREQUENCY'] = $orderSubscribe->getFrequency();

            } catch (\Exception $e) {
                $result->addError(new Error(
                    sprintf("Не удалось активировать 2 шаг: %s", $e->getMessage())
                ));
            }
        }

        if(!$result->isSuccess()){
            $this->arResult['CURRENT_STAGE'] = 'error';
            $this->arResult['ERROR'] = $result->getErrorMessages();
        } else {
            $this->arResult['CURRENT_STAGE'] = 'step2';
        }

        return $result;
    }

    protected function initPostFields()
    {
        $this->arResult['~FIELD_VALUES'] = $this->request->getPostList()->toArray();
        $this->arResult['FIELD_VALUES'] = $this->walkRequestValues($this->arResult['~FIELD_VALUES']);
    }

    /**
     * @throws ApplicationCreateException
     * @throws Exception
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     */
    protected function processSubscribeFormFields()
    {
        $fieldName = 'deliveryDate';
        $value = $this->arResult['FIELD_VALUES'][$fieldName] ?? '';
        if ($value === '') {
            $this->setFieldError($fieldName, 'Значение не задано', 'empty');
        } else {
            if(!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value)) {
                $this->setFieldError($fieldName, 'Значение задано некорректно', 'not_valid');
            } else {
                if (!$GLOBALS['DB']->IsDate($value, 'DD.MM.YYYY')) {
                    $this->setFieldError($fieldName, 'Значение задано некорректно', 'not_valid');
                }
            }
        }

        if(!$this->arResult['FIELD_VALUES']['changeNextDelivery']){
            $fieldName = 'subscribeFrequency';
            $value = $this->arResult['FIELD_VALUES'][$fieldName] ?? '';
            $value = (int)$value;
            if ($value == 0) {
                $this->setFieldError($fieldName, 'Значение не задано', 'empty');
            } else {
                $success = false;
                $deliveryFrequency = $this->getFrequencyVariants();

                foreach ($deliveryFrequency as $variant) {
                    if ($variant['VALUE'] == $value) {
                        $success = true;
                        break;
                    }
                }
                if (!$success) {
                    $this->setFieldError($fieldName, 'Значение задано некорректно', 'not_valid');
                }
            }
        }

        /*$fieldName = 'deliveryInterval';
        $value = $this->arResult['FIELD_VALUES'][$fieldName] ?? '';
        $timeIntervals = $this->getTimeVariants();
        if ($value === '' && $timeIntervals) {
            $this->setFieldError($fieldName, 'Значение не задано', 'empty');
        } elseif ($value !== '') {
            $success = false;
            foreach ($timeIntervals as $variant) {
                if ($variant['VALUE'] == $value) {
                    $success = true;
                    break;
                }
            }
            if (!$success) {
                $this->setFieldError($fieldName, 'Значение задано некорректно', 'not_valid');
            }
        }*/
    }

    /**
     * @return Order|null
     * @throws ApplicationCreateException
     * @throws Exception
     */
    public function getOrder()
    {
        if (!isset($this->data['ORDER'])) {
            $this->data['ORDER'] = null;
            $orderId = $this->arParams['ORDER_ID'];

            if($this->arParams['SUBSCRIBE_ID'] > 0){
                $orderId = $this->getOrderSubscribe()->getOrderId();
            }

            if ($orderId <= 0) {
                $this->setExecError('getOrder', 'Некорректный идентификатор заказа', 'incorrectOrderId');
            } elseif ($this->arParams['USER_ID'] <= 0) {
                $this->setExecError('getOrder', 'Некорректный идентификатор пользователя', 'incorrectUserId');
            } else {
                $this->getOrderSubscribeService()->getPersonalOrderService()->clearOrderRepositoryNav(); // выставлен, если получаем следующую страницу в личном кабинете
                $orderSubscribeService = $this->getOrderSubscribeService();
                /** @var Order $order */
                $order = $orderSubscribeService->getOrderById($orderId);
                if ($order) {
                    if ($order->getUserId() === $this->arParams['USER_ID']) {
                        $this->data['ORDER'] = $order;
                    } else {
                        $this->setExecError(
                            'getOrder',
                            'Нельзя подписаться на заказ под данным пользователем',
                            'notThisUserOrder'
                        );
                    }
                } else {
                    $this->setExecError('getOrder', 'Заказ не найден', 'orderNotFound');
                }
            }
        }

        return $this->data['ORDER'];
    }

    /**
     * @return OrderSubscribe|null
     * @throws ApplicationCreateException
     * @throws Exception
     */
    public function getOrderSubscribe($strict = true)
    {
        if (!isset($this->data['ORDER_SUBSCRIBE'])) {
            $this->data['ORDER_SUBSCRIBE'] = null;

            if($this->arParams['SUBSCRIBE_ID'] > 0){
                $this->data['ORDER_SUBSCRIBE'] = $this->getOrderSubscribeService()->getById($this->arParams['SUBSCRIBE_ID']);
            } else if(!$strict && $this->arParams['ORDER_ID'] > 0) {
                $order = $this->getOrder();
                if ($order) {
                    $orderSubscribeService = $this->getOrderSubscribeService();
                    $collection = $orderSubscribeService->getSubscriptionsByOrder(
                        $order->getId(),
                        false
                    );
                    $this->data['ORDER_SUBSCRIBE'] = $collection->count() ? $collection->first() : null;
                }
            }
        }

        return $this->data['ORDER_SUBSCRIBE'];
    }

    public function flushOrderSubscribe()
    {
        if (isset($this->data['ORDER_SUBSCRIBE'])) {
            unset($this->data['ORDER_SUBSCRIBE']);
        }
    }

    /**
     * Сброс тегированного кеша
     */
    public function clearTaggedCache()
    {
        $clearTags = [];
        if ($this->arParams['ORDER_ID']) {
            $clearTags[] = 'order:item:'.$this->arParams['ORDER_ID'];
        }
        if ($this->arParams['USER_ID']) {
            $clearTags[] = 'order:'.$this->arParams['USER_ID'];
        }
        if ($clearTags) {
            TaggedCacheHelper::clearManagedCache($clearTags);
        }
    }

    /**
     * Варианты времени доставки
     *
     * @return array
     * @throws ApplicationCreateException
     * @throws Exception
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     */
    public function getTimeVariants(): array
    {
        if (!isset($this->data['TIME_VARIANTS'])) {
            $this->data['TIME_VARIANTS'] = [];

            /** @var Order $order */
            $order = $this->getOrder();
            $bitrixOrder = $order ? $order->getBitrixOrder() : null;
            if ($bitrixOrder) {
                try {
                    $subscribeService = $this->getOrderSubscribeService();
                    $calculationResult = $subscribeService->getDeliveryCalculationResult(
                        $this->getOrderSubscribe()
                    );
                    $data = $calculationResult ? $calculationResult->getData() : [];
                    $intervals = $data['INTERVALS'] ?? null;
                    if ($intervals && $intervals instanceof IntervalCollection) {
                        foreach ($intervals as $interval) {
                            /** @var Interval $interval */
                            $val = $interval->__toString();
                            $val = str_replace(' ', '', $val);
                            $this->data['TIME_VARIANTS'][] = [
                                'VALUE' => $val,
                                'TEXT' => $val,
                            ];
                        }
                    }
                } catch (\Exception $exception) {
                    $this->setExecError(
                        'getTimeVariants',
                        $exception->getMessage(),
                        'calculationResultException'
                    );
                }
            } else {
                $this->setExecError(
                    'getTimeVariants',
                    'Заказ не найден',
                    'orderNotFound'
                );
            }
        }

        return $this->data['TIME_VARIANTS'];
    }

    /**
     * Варианты периодичности доставки
     *
     * @return array
     * @throws ApplicationCreateException
     * @throws Exception
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public function getFrequencyVariants(): array
    {
        if (!isset($this->data['FREQUENCY_VARIANTS'])) {
            $this->data['FREQUENCY_VARIANTS'] = [];
            $collection = $this->getOrderSubscribeService()->getFrequencyEnum();
            foreach ($collection as $item) {
                /** @var UserFieldEnumValue $item */
                $this->data['FREQUENCY_VARIANTS'][] = [
                    'VALUE' => $item->getId(),
                    'TEXT' => $item->getValue(),
                ];
            }
        }

        return $this->data['FREQUENCY_VARIANTS'];
    }

    /**
     * @param array|string $errorMsg
     * @return string
     */
    protected function prepareErrorMsg($errorMsg)
    {
        $result = '';
        if (is_array($errorMsg)) {
            $result = [];
            foreach ($errorMsg as $item) {
                if ($item instanceof Error) {
                    if ($item->getCode()) {
                        $result[] = '['.$item->getCode().'] '.$item->getMessage();
                    } else {
                        $result[] = $item->getMessage();
                    }
                } elseif (is_scalar($item)) {
                    $result[] = $item;
                }
            }
            $result = implode('<br>', $result);
        } elseif (is_scalar($errorMsg)) {
            $result = $errorMsg;
        }

        return $result;
    }

    /**
     * @param string $fieldName
     * @return string
     */
    public function getFieldCaption(string $fieldName)
    {
        return $this->fieldCaptions[$fieldName] ?? '';
    }

    /**
     * @param string $fieldName
     * @param array|string $errorMsg
     * @param string $errCode
     */
    protected function setFieldError(string $fieldName, $errorMsg, string $errCode = '')
    {
        $errorMsg = $this->prepareErrorMsg($errorMsg);
        $this->arResult['ERROR']['FIELD'][$fieldName] = new Error($errorMsg, $errCode);
        //$this->log()->debug(sprintf('$fieldName: %s; $errorMsg: %s; $errCode: %s', $fieldName, $errorMsg, $errCode));
    }

    /**
     * @param string $errName
     * @param array|string $errorMsg
     * @param string $errCode
     */
    protected function setExecError(string $errName, $errorMsg, $errCode = '')
    {
        $errorMsg = $this->prepareErrorMsg($errorMsg);
        $this->arResult['ERROR']['EXEC'][$errName] = new Error($errorMsg, $errCode);
        //$this->log()->debug(sprintf('$fieldName: %s; $errorMsg: %s; $errCode: %s', $fieldName, $errorMsg, $errCode));
    }

    /**
     * @return string
     */
    protected function getExecErrors()
    {
        $result = '';

        /** @var Error $error */
        foreach ($this->arResult['ERROR']['EXEC'] as $errName => $error){
            $result .= sprintf("\r\n %s: %s", $errName, $error->getMessage());
        }
        return $result;
    }

    /**
     * @return string
     */
    protected function getFieldErrors()
    {
        $result = '';

        /** @var Error $error */
        foreach ($this->arResult['ERROR']['FIELD'] as $errName => $error){
            $result .= sprintf("\r\n %s: %s", $errName, $error->getMessage());
        }
        return $result;
    }

    /**
     * @param $value
     * @return array|mixed|string
     */
    protected function walkRequestValues($value)
    {
        if (is_scalar($value)) {
            return htmlspecialcharsbx($value);
        } elseif (is_array($value)) {
            return array_map(
                [$this, __FUNCTION__],
                $value
            );
        }

        return $value;
    }

    /**
     * @param Order $order
     * @return \DateTime|null
     * @throws ApplicationCreateException
     * @throws Exception
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getOrderPossibleDeliveryDate(Order $order): ?\DateTime
    {
        $deliveryDate = null;
        $bitrixOrder = $order->getBitrixOrder();
        $cityCode = $order->getProperty('CITY_CODE');
//        if($cityCode !== null && $cityCode) {
//            /** @var \FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult $deliveryCalcResult */
//            $deliveryCalcResult = $this->getOrderSubscribeService()->getDeliveryCalculationResult(
//                $this->getOrderSubscribe()
//            );
//            if ($deliveryCalcResult !== null) {
//                $deliveryDate = $this->getOrderSubscribeService()->getOrderDeliveryDate(
//                    $deliveryCalcResult
//                );
//            }
//        }

        return $deliveryDate;
    }

    /**
     * @param int $offerId
     *
     * @return ResizeImageDecorator|null
     */
    public function getImage(int $offerId): ?ResizeImageDecorator
    {
        if ($offerId <= 0) {
            return null;
        }

        if (!isset($this->images[$offerId])) {
            $offer = $this->getOffer($offerId);
            $image = null;
            if ($offer !== null) {
                $images = $offer->getResizeImages(110, 110);
                $this->images[$offerId] = $images->first();
            }
        }
        return $this->images[$offerId];
    }

    /**
     * @param int $offerId
     *
     * @return Offer|null
     */
    public function getOffer(int $offerId): ?Offer
    {
        if ($offerId <= 0) {
            return null;
        }
        if (!isset($this->offers[$offerId])) {
            $this->offers[$offerId] = OfferQuery::getById($offerId);
        }
        return $this->offers[$offerId];
    }

    /**
     * @param array $offerIds
     * @return bool
     */
    public function setOffers(array $offerIds): bool
    {
        if (count($offerIds) <= 0) {
            return false;
        }

        $offers = (new OfferQuery())
            ->withFilter(['ID' => $offerIds])
            ->exec();

        if($offers->isEmpty()){
            return false;
        }

        /** @var Offer $offer */
        foreach($offers as $offer){
            $this->offers[$offer->getId()] = $offer;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getOffers()
    {
        return $this->offers;
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function getItemsFormatted()
    {
        $items = [];
        $basket = $this->getBasket();

        /** @var BasketItem $basketItem */
        foreach($basket as $basketItem){
            $items[$basketItem->getProductId()] = [
                'quantity' => $basketItem->getQuantity(),
                'productId' => $basketItem->getProductId(),
            ];
        }
        return $items;
    }

    /**
     * @param array $items
     * @return \Bitrix\Sale\BasketBase
     * @throws ApplicationCreateException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectException
     * @throws Exception
     */
    public function createBasketFromItems(array $items)
    {
        $basket = Basket::create(SITE_ID);
        $tItems = [];
        foreach($items as $item){
            $tItems[$item['productId']] = [
                'OFFER_ID' => $item['productId'],
                'QUANTITY' => $item['quantity']
            ];
        }

        $offerIds = array_column($tItems, 'OFFER_ID');
        if(empty($offerIds)){
            throw new Exception("Empty offerIds");
        }

        $offers = (new OfferQuery())
            ->withFilter(["ID" => $offerIds])
            ->exec();

        /** @var Offer $offer */
        foreach($offers as $offer){
            $tItems[$offer->getId()]['PRICE'] = $offer->getSubscribePrice();
            $tItems[$offer->getId()]['BASE_PRICE'] = $offer->getPrice();
            $tItems[$offer->getId()]['NAME'] = $offer->getName();
            $tItems[$offer->getId()]['WEIGHT'] = $offer->getCatalogProduct()->getWeight();
            $tItems[$offer->getId()]['DETAIL_PAGE_URL'] = $offer->getDetailPageUrl();
            $tItems[$offer->getId()]['PRODUCT_XML_ID'] = $offer->getXmlId();
            if($tItems[$offer->getId()]['QUANTITY'] > $offer->getQuantity()){
                $tItems[$offer->getId()]['QUANTITY'] = $offer->getQuantity();
            }
        }

        foreach($tItems as $item){
            $basketItem = BasketItem::create($basket, 'sale', $item['OFFER_ID']);
            $basketItem->setFields([
                'PRICE'                  => $item['PRICE'],
                'BASE_PRICE'             => $item['BASE_PRICE'],
                'CUSTOM_PRICE'           => BitrixUtils::BX_BOOL_TRUE,
                'QUANTITY'               => $item['QUANTITY'],
                'CURRENCY'               => CurrencyManager::getBaseCurrency(),
                'NAME'                   => $item['NAME'],
                'WEIGHT'                 => $item['WEIGHT'],
                'DETAIL_PAGE_URL'        => $item['DETAIL_PAGE_URL'],
                'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
                'CATALOG_XML_ID'         => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS),
                'PRODUCT_XML_ID'         => $item['PRODUCT_XML_ID'],
                'CAN_BUY'                => "Y",
            ]);

            /** @noinspection PhpInternalEntityUsedInspection */
            $basket->addItem($basketItem);
        }

        return $basket;
    }

    /**
     * @param null $basket
     * @return array|\FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface[]
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Sale\UserMessageException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getDeliveries($basket = null)
    {
        if (null === $this->deliveries && $basket) {
            $order = \Bitrix\Sale\Order::create(SITE_ID, $this->getUserService()->getCurrentFUserId() ?: null);
            $order->setBasket($basket);
            // todo: добавить коды служб
            $codes = [];

            $this->deliveries = $this->getDeliveryService()->getByBasket(
                $basket,
                $this->getUserService()->getSelectedCity()['CODE'],
                $codes
            );
        }

        return $this->deliveries;
    }

    /**
     * @return \FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface|mixed
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Sale\UserMessageException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getSelectedDelivery()
    {
        $deliveries = $this->getDeliveries();
        if($this->getSubscribe()){
//            $deliveries = array_filter($deliveries, function($delivery){
//                /** @var BaseResult $delivery */
//                return $delivery->getDeliveryId() == $this->getSubscribe()->getDeliveryId();
//            });

            foreach ($deliveries as $delivery){
                if($delivery->getDeliveryId() == $this->getSubscribe()->getDeliveryId()){
                    return $delivery;
                }
            }
        }

        $selectedDelivery = current($deliveries);
        if (!$selectedDelivery) {
            throw new NotFoundException('в данный момент нет подходящих служб доставки для оформления подписки на указанный список товаров');
        }

        return $selectedDelivery;
    }

    /**
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Sale\UserMessageException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws Exception
     */
    public function getPickupData($deliveries = null)
    {
        $pickup = null;
        if(null === $deliveries){
            $deliveries = $this->$this->getDeliveries();
        }
        foreach ($deliveries as $calculationResult) {
            if ($this->getDeliveryService()->isPickup($calculationResult)) {
                $pickup = $calculationResult;
            }
        }

        /* @var PickupResultInterface $pickup */
        if (null !== $pickup) {
            $this->arResult['SELECTED_SHOP'] = $pickup->getSelectedShop();
            if ($pickup->getSelectedShop()->getMetro()) {
                $this->arResult['METRO'] = $this->getStoreService()->getMetroInfo(
                    ['ID' => $pickup->getSelectedShop()->getMetro()]
                );
            }
        }
    }

    /**
     * @param BasketBase $basket
     * @return array
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function getBasketItemData(BasketBase $basket)
    {
        $itemData = [];
        $basket = $this->getBasket();
        /** @var BasketItem $item */
        foreach ($basket as $item) {
            $offerId = (int)$item->getProductId();
            /** @var Offer $offer */
            foreach ($this->getBasketService()->getOfferCollection() as $offer) {
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
     * @return PaymentCollection
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectException
     */
    public function getPayments()
    {
        if (!$this->paymentCollection) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $order = \Bitrix\Sale\Order::create(
                SITE_ID,
                null,
                CurrencyManager::getBaseCurrency()
            );
            $this->paymentCollection = $order->getPaymentCollection();
            $sum = $this->getBasket()
                ->getOrderableItems()
                ->getPrice();

            $extPayment = $this->paymentCollection->createItem();
            $extPayment->setField('SUM', $sum);
        }

        return $this->paymentCollection;
    }

    /**
     * @param bool $withInner
     * @param bool $filter
     * @param float $basketPrice
     * @return array
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\SystemException
     */
    public function getAvailablePayments($withInner = false, $filter = true, float $basketPrice = 0): array
    {
        $paymentCollection = $this->getPayments();

        $payments = [];
        /** @var Payment $payment */
        foreach ($paymentCollection as $payment) {
            if ($payment->isInner()) {
                continue;
            }

            $payments = PaySystemManager::getListWithRestrictions($payment);
        }

        if (!$withInner) {
            $innerPaySystemId = (int)PaySystemManager::getInnerPaySystemId();
            /** @var Payment $payment */
            foreach ($payments as $id => $payment) {
                if ($innerPaySystemId === $id) {
                    unset($payments[$id]);
                    break;
                }
            }
        }

        // для подписки толкьо оплата при получении
        foreach ($payments as $id => $payment) {
            if (!in_array($payment['CODE'], $this->getOrderSubscribeService()->getPaymentCodes())) {
                unset($payments[$id]);
                break;
            }
        }

        // если есть оплата "наличными или картой", удаляем оплату "наличными",
        if ($filter
            && !empty(\array_filter($payments, function ($item) {
                return $item['CODE'] === OrderPayment::PAYMENT_CASH_OR_CARD;
            }))) {
            foreach ($payments as $id => $payment) {
                if ($payment['CODE'] === OrderPayment::PAYMENT_CASH) {
                    unset($payments[$id]);
                    break;
                }
            }
        }

        return $payments;
    }

    /**
     * @return \Bitrix\Main\HttpRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @throws ApplicationCreateException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getSubscribePrice()
    {
        // логика взята из BasketComponent::calcSubscribeFields
        $subscribePrice = 0;

        /** @var Basket $basket */
        $basket = $this->arResult['ORDER']->getBitrixOrder()->getBasket();

        /** @var BasketItem $basketItem */
        $orderableBasket = $basket->getOrderableItems();

        foreach ($orderableBasket as $basketItem) {
            if (!isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
                $offer = $this->getOffer((int)$basketItem->getProductId());
                if (!$offer) {
                    continue;
                }

                $priceSubscribe = $offer->getSubscribePrice() * $basketItem->getQuantity();
                $priceDefault = $basketItem->getPrice() * $basketItem->getQuantity();
                $price = $priceDefault;
                if ($priceSubscribe < $priceDefault) {
                    $price = $priceSubscribe;
                }

                $subscribePrice += $price;
            }
        }

        $this->arResult['SUBSCRIBE_PRICE'] = $subscribePrice;
    }

    /**
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function isBySubscribe()
    {
        // если на этот заказ оформлена подписка, то заказ не может быть создан по подписке
        if ($this->arResult['ORDER_SUBSCRIBE']) {
            $this->arResult['BY_SUBSCRIBE'] = false;
            return;
        }

        if (($this->arResult['ORDER']) && ($this->arResult['ORDER']->getId())) {
            $this->arResult['BY_SUBSCRIBE'] = $this->getOrderSubscribeHistoryService()->hasOriginOrder($this->arResult['ORDER']->getId());
        } else {
            $this->arResult['BY_SUBSCRIBE'] = false;
        }
    }

    /**
     * Режим редактирования единичной доставки
     *
     * @return bool
     */
    public function isRenewalAction()
    {
        return $this->getActionReal() == 'renewalSubmit';
    }

    /**
     * @return false|OrderSubscribeSingle
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getSingleSubscribe()
    {
        if(null === $this->orderSubscribeSingle){
            $this->orderSubscribeSingle = $this->getOrderSubscribeService()->getSingleSubscribe($this->arParams['SUBSCRIBE_ID']);
        }

        return $this->orderSubscribeSingle;
    }

    /**
     * Режим редактирования всей подписки при наличии единичной
     *
     * @return bool
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function isSingleSubscribeMode()
    {
        return !$this->arParams['IS_SINGLE_SUBSCRIBE']
            && $this->getSingleSubscribe()
            && !$this->isRenewalAction();
    }
}
