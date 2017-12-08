<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Sale\Shipment;

class InnerDeliveryService extends DeliveryServiceBase
{
    protected $availableZones = [
        self::ZONE_1,
        self::ZONE_2,
    ];

    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
    }

    public static function getClassTitle()
    {
        return 'Доставка "Четыре лапы"';
    }

    public static function getClassDescription()
    {
        return 'Обработчик собственной доставки "Четыре лапы"';
    }

    public function isCompatible(Shipment $shipment)
    {
        if (!parent::isCompatible($shipment)) {
            return false;
        }

        /** todo проверка остатков товаров */

        return true;
    }

    protected function calculateConcrete(Shipment $shipment)
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
