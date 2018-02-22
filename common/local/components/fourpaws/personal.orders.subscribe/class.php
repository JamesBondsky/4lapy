<?php

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Error;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsPersonalCabinetOrdersSubscribeComponent extends CBitrixComponent
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

    public function onPrepareComponentParams($params)
    {
        $this->arResult['ORIGINAL_PARAMETERS'] = $params;

        $params['CACHE_TYPE'] = $params['CACHE_TYPE'] ?? 'A';
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 3600;

        try {
            $params['USER_ID'] = $this->getUserService()->getCurrentUserId();
        } catch (\Exception $exception) {
            $params['USER_ID'] = 0;
        }

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
            $this->log()->critical(sprintf('%s exception: %s', __FUNCTION__, $exception->getMessage()));
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
     * @throws ApplicationCreateException
     * @throws Exception
     */
    protected function initialLoadAction()
    {
        $this->loadData();
    }

    /**
     * @throws ApplicationCreateException
     * @throws Exception
     */
    protected function loadData()
    {
        $orderSubscribeService = $this->getOrderSubscribeService();
        $filterActive = true;
        $this->arResult['ORDERS'] = $orderSubscribeService->getUserSubscribedOrders(
            $this->arParams['USER_ID'],
            $filterActive
        );

        // в коллекции значения с неправильными ключами,
        // костыляем, чтобы в шаблоне не гонять filter
        $tmpCollection = $orderSubscribeService->getSubscriptionsByUser(
            $this->arParams['USER_ID'],
            $filterActive
        );
        $subscriptions = new ArrayCollection();
        foreach ($tmpCollection as $collectionItem) {
            /** @var OrderSubscribe $collectionItem */
            $subscriptions->offsetSet($collectionItem->getOrderId(), $collectionItem);
        }
        $this->arResult['SUBSCRIPTIONS'] = $subscriptions;

        $this->includeComponentTemplate();
    }
}
