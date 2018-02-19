<?php

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\PersonalBundle\Entity\Order;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class FourPawsPersonalCabinetOrderItemComponent extends CBitrixComponent
{
    use LazyLoggerAwareTrait;

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
}
