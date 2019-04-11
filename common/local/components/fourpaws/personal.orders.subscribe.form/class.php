<?php

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
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
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Service\PiggyBankService;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\ShopInfoService;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;

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

    /** @var DeliveryService $deliveryService */
    private $deliveryService;

    /** @var StoreService $storeService */
    private $storeService;

    /** @var ShopInfoService $shopListService */
    private $shopListService;

    /** @var BasketService $basketService */
    private $basketService;

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
            $this->setAction($this->prepareAction());
            $this->doAction();
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
    protected function prepareAction()
    {
        $action = 'initialLoad';

        switch ($this->request->get('action')) {
            case 'deliveryOrderSubscribe':
                $action = 'subscribe';
                break;
            case 'deliveryOrderUnsubscribe':
                $action = 'unsubscribe';
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

    protected function initialLoadAction()
    {
        $this->loadData();
    }

    /**
     * @throws ApplicationCreateException
     * @throws Exception
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    protected function subscribeAction()
    {
        /** @todo подписка для оффлайн заказов - заказы из манзаны */
        $this->initPostFields();
        if ($this->arResult['FIELD_VALUES']['orderId']) {
            $this->arParams['ORDER_ID'] = (int)$this->arResult['FIELD_VALUES']['orderId'];
        }

        $this->arResult['SUBSCRIBE_ACTION']['SUCCESS'] = 'N';

        $this->processSubscribeFormFields();

        if (empty($this->arResult['ERROR']['FIELD'])) {
            $order = $this->getOrder();
            if ($order) {
                $fields = [
                    'UF_ACTIVE' => 1,
                    'UF_ORDER_ID' => $order->getId(),
                    'UF_DATE_START' => $this->arResult['FIELD_VALUES']['dateStart'] ?? '',
                    'UF_FREQUENCY' => $this->arResult['FIELD_VALUES']['deliveryFrequency'] ?? '',
                    'UF_DELIVERY_TIME' => $this->arResult['FIELD_VALUES']['deliveryInterval'] ?? '',
                ];

                $orderSubscribeService = $this->getOrderSubscribeService();
                $orderSubscribe = $this->getOrderSubscribe();
                if ($orderSubscribe) {
                    // подписка уже есть, обновляем
                    $this->arResult['SUBSCRIBE_ACTION']['SUBSCRIPTION_ID'] = $orderSubscribe->getId();
                    $this->arResult['SUBSCRIBE_ACTION']['TYPE'] = 'UPDATE';
                    $this->arResult['SUBSCRIBE_ACTION']['RESUMED'] = $orderSubscribe->isActive() ? 'N' : 'Y';
                    // сбрасываем дату последней проверки необходимости создания заказа
                    $fields['UF_LAST_CHECK'] = '';
                    try {
                        $updateResult = $orderSubscribeService->update(
                            $orderSubscribe->getId(),
                            $fields
                        );
                        if ($updateResult->isSuccess()) {
                            $this->arResult['SUBSCRIBE_ACTION']['SUCCESS'] = 'Y';

                            // отправка уведомления о созданной подписке (в данном случае - возобновленной)
                            if ($this->arResult['SUBSCRIBE_ACTION']['RESUMED'] === 'Y') {
                                $this->sendOrderSubscribedNotification(
                                    $orderSubscribe->getId()
                                );
                            }

                            $this->flushOrderSubscribe();
                            $this->clearTaggedCache();
                        } else {
                            $this->setExecError('subscribeAction', $updateResult->getErrors(), 'subscriptionUpdate');
                        }
                    } catch (\Exception $exception) {
                        $this->setExecError('subscribeAction', $exception->getMessage(), 'subscriptionUpdateException');
                    }
                } else {
                    // создание новой подписки
                    $this->arResult['SUBSCRIBE_ACTION']['TYPE'] = 'CREATE';
                    $addResult = $orderSubscribeService->add($fields);
                    if ($addResult->isSuccess()) {
                        $this->arResult['SUBSCRIBE_ACTION']['SUCCESS'] = 'Y';
                        $this->arResult['SUBSCRIBE_ACTION']['SUBSCRIPTION_ID'] = $addResult->getId();

                        // отправка уведомления о созданной подписке
                        $this->sendOrderSubscribedNotification($addResult->getId());

                        $this->flushOrderSubscribe();
                        $this->clearTaggedCache();
                    } else {
                        $this->setExecError('subscribeAction', $addResult->getErrors(), 'subscriptionAdd');
                    }
                }
            }
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

        $order = $this->getOrder();
        if ($order) {
            $orderSubscribeService = $this->getOrderSubscribeService();
            $orderSubscribe = $this->getOrderSubscribe();
            if ($orderSubscribe) {
                $this->arResult['UNSUBSCRIBE_ACTION']['SUBSCRIPTION_ID'] = $orderSubscribe->getId();
                // не удаляем запись, а деактивируем
                try {
                    $updateResult = $orderSubscribeService->update(
                        $orderSubscribe->getId(),
                        [
                            'UF_ACTIVE' => 0,
                        ]
                    );
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
            } else {
                $this->setExecError('unsubscribeAction', 'Подписка на заказ не найдена', 'subscriptionNotFound');
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
            $result = new Result();

            // получение контролов
            if(null === $this->arParams['STEP']){
                $this->arResult['ORDER'] = $this->getOrder();
                $this->arResult['CURRENT_STAGE'] = 'initial';
            }

            // получение формы
            if($this->arParams['STEP'] == 1){
                if($this->arParams['SUBSCRIBE_ID'] > 0){ // редактирование подписки
                    try {
                        $basket = $this->getOrderSubscribeService()->getBasketBySubscribeId($this->arParams['SUBSCRIBE_ID']);
                        $this->arResult['TITLE'] = "Редактирование подписки";
                    } catch (\Exception $e) {
                        $result->addError(new Error(
                            sprintf("Failed to get basket for form: %s", $e->getMessage()),
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
                        foreach ($basket as $basketItem){
                            if (in_array($basketItem->getProductId(), $piggyBankService->getMarksIds(), false)){
                                $basket->deleteItem($basketItem->getInternalIndex());
                            }
                        }
                        $this->arResult['TITLE'] = "Создание подписки";
                    } catch (\Exception $e) {
                        $result->addError(new Error(
                            sprintf("Failed to get basket for form: %s", $e->getMessage()),
                            'getBasket',
                            ['id' => $this->arParams['ORDER_ID']]
                        ));
                    }
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
            } else if ($this->arParams['STEP'] == 2) {
                $this->arParams['ITEMS'] = [
                  [
                      'id' => 1,
                      'productId' => 70833,
                      'quantity' => 1,
                  ],
                  [
                      'id' => 2,
                      'productId' => 35129,
                      'quantity' => 2,
                  ],
                  [
                      'id' => 3,
                      'productId' => 84355,
                      'quantity' => 2,
                  ],
                ];

                if($this->arParams['SUBSCRIBE_ID'] > 0){
                    $this->arResult['SUBSCRIBE'] = $this->setSubscribe($this->getOrderSubscribeService()->getById($this->arParams['SUBSCRIBE_ID']));
                }

                try {
                    $basket = $this->createBasketFromItems($this->arParams['ITEMS']);
                    $this->setBasket($basket);
                } catch (\Exception $e) {
                    $result->addError(new Error(
                        sprintf("Failed to get basket for form: %s", $e->getMessage())
                    ));
                }

                if($result->isSuccess()){
                    $selectedCity     = $this->getUserService()->getSelectedCity();
                    $deliveries       = $this->getDeliveries($basket);
                    $selectedDelivery = $this->getSelectedDelivery();

                    $this->getPickupData($deliveries);

                    $addresses = null;
                    if ($this->getUserService()->getCurrentUserId()) {
                        /** @var AddressService $addressService */
                        $addressService = Application::getInstance()->getContainer()->get('address.service');
                        $addresses      = $addressService->getAddressesByUser($this->getUserService()->getCurrentUserId(), $selectedCity['CODE']);
                    }

                    $delivery = null;
                    $pickup   = null;
                    foreach ($deliveries as $calculationResult) {
                        if ($this->deliveryService->isPickup($calculationResult)) {
                            $pickup = $calculationResult;
                        } elseif ($this->deliveryService->isDelivery($calculationResult)) {
                            $delivery = $calculationResult;
                        }
                    }

                    $this->arResult['PICKUP']               = $pickup;
                    $this->arResult['DELIVERY']             = $delivery;
                    $this->arResult['ADDRESSES']            = $addresses;
                    $this->arResult['SELECTED_DELIVERY']    = $selectedDelivery;
                    $this->arResult['PICKUP_AVAILABLE_PAYMENTS'] = $this->getAvailablePayments();
                }


                if(!$result->isSuccess()){
                    $this->arResult['CURRENT_STAGE'] = 'error';
                    $this->arResult['ERROR'] = $result->getErrorMessages();
                } else {
                    $this->arResult['CURRENT_STAGE'] = 'step2';
                }
            }


//            $this->arResult['ORDER'] = $this->getOrder();
//            if ($this->arResult['ORDER']) {
//                $this->arResult['FREQUENCY_VARIANTS'] = $this->getFrequencyVariants();
//                $this->arResult['TIME_VARIANTS'] = $this->getTimeVariants();
//                $this->arResult['ORDER_SUBSCRIBE'] = $this->getOrderSubscribe();
//            }
        }

        if ($this->arParams['INCLUDE_TEMPLATE'] !== 'N') {
            $this->includeComponentTemplate();
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
    protected function processSubscribeFormFields()
    {
        $fieldName = 'dateStart';
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

        $fieldName = 'deliveryFrequency';
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

        $fieldName = 'deliveryInterval';
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
        }
    }

    protected function initPostFields()
    {
        $this->arResult['~FIELD_VALUES'] = $this->request->getPostList()->toArray();
        $this->arResult['FIELD_VALUES'] = $this->walkRequestValues($this->arResult['~FIELD_VALUES']);
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
            if ($this->arParams['ORDER_ID'] <= 0) {
                $this->setExecError('getOrder', 'Некорректный идентификатор заказа', 'incorrectOrderId');
            } elseif ($this->arParams['USER_ID'] <= 0) {
                $this->setExecError('getOrder', 'Некорректный идентификатор пользователя', 'incorrectUserId');
            } else {
                $orderSubscribeService = $this->getOrderSubscribeService();
                /** @var Order $order */
                $order = $orderSubscribeService->getOrderById($this->arParams['ORDER_ID']);
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
    public function getOrderSubscribe()
    {
        if (!isset($this->data['ORDER_SUBSCRIBE'])) {
            $this->data['ORDER_SUBSCRIBE'] = null;
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
        if (!isset($this->offers)) {
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
        foreach($basket as $i => $basketItem){
            $id = $i+1;
            $items[$id] = [
                'id' => $id,
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
            $deliveries = array_filter($deliveries, function($delivery){
                /** @var BaseResult $delivery */
                return $delivery->getDeliveryId() == $this->getSubscribe()->getDeliveryId();
            });
        }

        $selectedDelivery = current($deliveries);
        if (!$selectedDelivery) {
            throw new NotFoundException('No deliveries available');
        }

        /*if ($selectedDelivery instanceof PickupResultInterface && $storage->getDeliveryPlaceCode()) {
            $selectedDelivery->setSelectedShop($this->getSelectedShop($storage, $selectedDelivery));
            if (!$selectedDelivery->isSuccess()) {
                $selectedDelivery->setSelectedShop($selectedDelivery->getBestShops()
                    ->first());
            }
        }*/

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

}
