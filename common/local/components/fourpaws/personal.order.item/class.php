<?php

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Application;
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

        $params['CACHE_TYPE'] = $params['CACHE_TYPE'] ?? 'N';
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 0;

        $params = parent::onPrepareComponentParams($params);

        return $params;
    }

    public function executeComponent()
    {
        if ($this->startResultCache()) {
            $this->arResult['ORDER'] = $this->arParams['ORDER'];
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
