<?php

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Application;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Entity\Order;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsPersonalCabinetOrderItemComponent extends CBitrixComponent
{
    use LazyLoggerAwareTrait;

    /** @var OrderSubscribeService $orderSubscribeService */
    private $orderSubscribeService = null;

    /**
     * FourPawsPersonalCabinetOrderItemComponent constructor.
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

    public function onPrepareComponentParams($params)
    {
        $params['ORDER'] = $params['ORDER'] ?? null;
        if (!$params['ORDER'] instanceof Order) {
            $params['ORDER'] = null;
        };

        $params['METRO'] = $params['METRO'] ?? null;

        $params['CACHE_TYPE'] = $params['CACHE_TYPE'] ?? 'A';
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 3600;

        // подстраховка для идентификатора кеша
        $params['ORDER_ID'] = $params['ORDER']->getId();

        $params = parent::onPrepareComponentParams($params);

        return $params;
    }

    /**
     * @return array
     * @throws \Bitrix\Main\SystemException
     */
    public function executeComponent()
    {
        /** @var Order $personalOrder */
        $personalOrder = $this->arParams['ORDER'];

        $cachePath = \Bitrix\Main\Application::getInstance()->getManagedCache()->getCompCachePath(
            $this->getRelativePath()
        );
        // к пути кеша добавляем идентификатор заказа
        $cachePath = $cachePath.'/'.$personalOrder->getId();

        if ($this->startResultCache(false, false, $cachePath)) {
            (new TaggedCacheHelper($cachePath))->addTag('order:item:'.$personalOrder->getId());

            $this->arResult['ORDER'] = $personalOrder;
            $this->arResult['METRO'] = $this->arParams['METRO'];

            $this->includeComponentTemplate();
        }

        return $this->arResult;
    }

    /**
     * @return OrderSubscribeService
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getOrderSubscribeService(): OrderSubscribeService
    {
        if (!$this->orderSubscribeService) {
            $appCont = Application::getInstance()->getContainer();
            $this->orderSubscribeService = $appCont->get('order_subscribe.service');
        }

        return $this->orderSubscribeService;
    }
}
