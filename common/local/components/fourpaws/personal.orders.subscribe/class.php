<?php

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\StoreBundle\Service\StoreService;
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
    protected $action = '';
    /** @var UserService $userCurrentUserService */
    protected $userCurrentUserService;
    /** @var OrderSubscribeService $orderSubscribeService */
    protected $orderSubscribeService = null;
    /** @var array $data */
    protected $data = [];
    /** @var StoreService $storeService */
    protected $storeService;

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
    public function getUserService(): UserService
    {
        if (!$this->userCurrentUserService) {
            $this->userCurrentUserService = Application::getInstance()->getContainer()->get(
                CurrentUserProviderInterface::class
            );
        }

        return $this->userCurrentUserService;
    }

    /**
     * @return StoreService
     * @throws ApplicationCreateException
     */
    public function getStoreService(): StoreService
    {
        if (!$this->storeService) {
            $this->storeService = Application::getInstance()->getContainer()->get(
                StoreService::class
            );
        }

        return $this->storeService;
    }

    /**
     * @return UserRepository
     * @throws ApplicationCreateException
     */
    public function getUserRepository(): UserRepository
    {
        return $this->getUserService()->getUserRepository();
    }

    /**
     * @return OrderSubscribeService
     * @throws ApplicationCreateException
     */
    public function getOrderSubscribeService(): OrderSubscribeService
    {
        if (!$this->orderSubscribeService) {
            $this->orderSubscribeService = Application::getInstance()->getContainer()->get(
                'order_subscribe.service'
            );
        }

        return $this->orderSubscribeService;
    }

    /**
     * @param string $action
     * @return void
     */
    protected function setAction($action): void
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    protected function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    protected function prepareAction(): string
    {
        $action = 'initialLoad';

        return $action;
    }

    protected function doAction(): void
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
    protected function initialLoadAction(): void
    {
        $this->loadData();
    }

    /**
     * @throws ApplicationCreateException
     * @throws Exception
     */
    protected function loadData(): void
    {
        $orderSubscribeService = $this->getOrderSubscribeService();
        $filterActive = true;
        /** $this->arResult['ORDERS'] ArrayCollection */
        $this->arResult['ORDERS'] = $orderSubscribeService->getUserSubscribedOrders(
            $this->arParams['USER_ID'],
            $filterActive
        );

        // в коллекции значения с неправильными ключами,
        // костыляем, чтобы в шаблоне не гонять filter
        $subscriptions = new ArrayCollection();
        if ($this->arResult['ORDERS'] && count($this->arResult['ORDERS'])) {
            $tmpCollection = $orderSubscribeService->getSubscriptionsByUser(
                $this->arParams['USER_ID'],
                $filterActive
            );

            foreach ($tmpCollection as $collectionItem) {
                /** @var OrderSubscribe $collectionItem */
                $subscriptions->offsetSet($collectionItem->getOrderId(), $collectionItem);
            }
        }
        $this->arResult['SUBSCRIPTIONS'] = $subscriptions;
        $this->arResult['METRO'] = new ArrayCollection($this->getStoreService()->getMetroInfo());

        $this->includeComponentTemplate();
    }
}
