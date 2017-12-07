<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Sale\Shipment;
use Bitrix\Sale\Delivery\Services\Base;

class InnerDelivery extends Base
{
    protected static $isCalculatePriceImmediately = true;

    protected static $whetherAdminExtraServicesShow = false;

    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
    }

    public static function getClassTitle()
    {
        return 'Доставка';
    }

    public static function getClassDescription()
    {
        return 'Обработчик доставки';
    }

    public function isCalculatePriceImmediately()
    {
        return self::$isCalculatePriceImmediately;
    }

    public static function whetherAdminExtraServicesShow()
    {
        return self::$whetherAdminExtraServicesShow;
    }

    protected function calculateConcrete(Shipment $shipment = null)
    {
        /* @todo calculate delivery time and price */

        $result = new \Bitrix\Sale\Delivery\CalculationResult();
        $result->setDeliveryPrice(100);

        return $result;
    }

    protected function getConfigStructure()
    {
        $result = [
            'MAIN' => [
                'TITLE'       => 'Основные',
                'DESCRIPTION' => 'Основные настройки',
                'ITEMS'       => [],
            ],
        ];

        return $result;
    }
}
