<?php

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Error;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Entity\UserFieldEnumValue;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\PersonalBundle\Entity\Order;
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
    private $orderSubscribeService = null;
    /** @var array $data */
    protected $data = [];
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
     * @return UserRepository
     * @throws ApplicationCreateException
     */
    public function getUserRepository()
    {
        return $this->getUserService()->getUserRepository();
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
            $this->arResult['ORDER'] = $this->getOrder();
            if ($this->arResult['ORDER']) {
                $this->arResult['TIME_VARIANTS'] = $this->getTimeVariants();
                $this->arResult['FREQUENCY_VARIANTS'] = $this->getFrequencyVariants();
                $this->arResult['ORDER_SUBSCRIBE'] = $this->getOrderSubscribe();
            }
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
                        $bitrixOrder
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
     * @return DateTime
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
        $bitrixOrder = $order->getBitrixOrder();
        $deliveryCalcResult = $this->getOrderSubscribeService()->getDeliveryCalculationResult(
            $bitrixOrder
        );
        if($deliveryCalcResult !== null) {
            $deliveryDate = $this->getOrderSubscribeService()->getOrderDeliveryDate(
                $deliveryCalcResult
            );

            return $deliveryDate;
        }
        return null;
    }
}
