<?php

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Error;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Service\DeliveryServiceInterface;
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
     * @return array
     * @throws Exception
     */
    public function executeComponent()
    {
        try {
            $this->setAction($this->prepareAction());
            $this->doAction();
        } catch (\Exception $exception) {
            $this->log()->critical(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
            throw $exception;
        }

        return $this->arResult;
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

        if ($this->request->get('action') === 'deliveryOrderSubscribe')  {
            $action = 'subscribe';
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
        $this->arResult['ORDER'] = $this->getOrderData();
        if ($this->arResult['ORDER']) {
            $this->arResult['TIME_VARIANTS'] = $this->getTimeVariants();
            $this->arResult['FREQUENCY_VARIANTS'] = $this->getFrequencyVariants();
        }

        $this->loadData();
    }

    protected function subscribeAction()
    {
        $this->initPostFields();
        if ($this->arResult['FIELD_VALUES']['orderId']) {
            $this->arParams['ORDER_ID'] = (int)$this->arResult['FIELD_VALUES']['orderId'];
        }

        $this->arResult['ORDER'] = $this->getOrderData();
        if ($this->arResult['ORDER']) {
            $this->arResult['TIME_VARIANTS'] = $this->getTimeVariants();
            $this->arResult['FREQUENCY_VARIANTS'] = $this->getFrequencyVariants();
        }

        $this->loadData();
    }

    protected function loadData()
    {
        if ($this->arParams['INCLUDE_TEMPLATE'] !== 'N') {
            $this->includeComponentTemplate();
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
    protected function getOrderData()
    {
        if (!isset($this->data['ORDER'])) {
            $this->data['ORDER'] = null;
            if ($this->arParams['ORDER_ID'] > 0 && $this->arParams['USER_ID'] > 0) {
                $orderSubscribeService = $this->getOrderSubscribeService();
                /** @var Order $order */
                $order = $orderSubscribeService->getOrderById($this->arParams['ORDER_ID']);
                if ($order) {
                    if ($order->getUserId() === $this->arParams['USER_ID']) {
                        $this->data['ORDER'] = $order;
                    } else {
                        $this->setExecError('notThisUserOrder', 'Нельзя подписаться на заказ под данным пользователем', 'notThisUserOrder');
                    }
                } else {
                    $this->setExecError('orderNotFound', 'Заказ не найден', 'orderNotFound');
                }
            }
        }

        return $this->data['ORDER'];
    }

    /**
     * Варианты времени доставки
     *
     * @return array
     * @throws ApplicationCreateException
     * @throws Exception
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     */
    protected function getTimeVariants(): array
    {
        if (!isset($this->data['TIME_VARIANTS'])) {
            $this->data['TIME_VARIANTS'] = [];

            /** @var Order $order */
            $order = $this->getOrderData();
            if ($order) {
                $bitrixOrder = $order->getBitrixOrder();
                foreach ($bitrixOrder->getShipmentCollection() as $shipment) {
                    /** @var \Bitrix\Sale\Shipment $shipment */
                    if ($shipment->isSystem()) {
                        continue;
                    }
                    $deliveryService = $shipment->getDelivery();
                    if ($deliveryService instanceof DeliveryServiceInterface) {
                        $intervals = $deliveryService->getIntervals($shipment);
                        if ($intervals) {
                            foreach($intervals as $interval) {
                                $from = str_pad($interval['FROM'], 2, 0, STR_PAD_LEFT).':00';
                                $to = str_pad($interval['TO'], 2, 0, STR_PAD_LEFT).':00';
                                $val = $from.'—'.$to;
                                $this->data['TIME_VARIANTS'][] = [
                                    'VALUE' => $val,
                                    'TEXT' => $val,
                                ];
                            }
                        }
                    }
                }
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
    protected function getFrequencyVariants(): array
    {
        if (!isset($this->data['FREQUENCY_VARIANTS'])) {
            $this->data['FREQUENCY_VARIANTS'] = [];
            $enum = $this->getOrderSubscribeService()->getFrequencyEnum();
            foreach ($enum as $item) {
                $this->data['FREQUENCY_VARIANTS'][] = [
                    'VALUE' => $item['XML_ID'],
                    'TEXT' => $item['VALUE'],
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
                    $result[] = '['.$item->getCode().'] '.$item->getMessage();
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
}
