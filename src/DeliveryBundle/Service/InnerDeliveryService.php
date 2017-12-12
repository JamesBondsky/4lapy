<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Main\Error;
use Bitrix\Sale\Shipment;

class InnerDeliveryService extends DeliveryServiceBase
{
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
        $result = parent::calculateConcrete($shipment);
        if (!$result->isSuccess()) {
            return $result;
        }

        $deliveryZone = $this->getDeliveryZoneCode($shipment);
        if ($this->config[$deliveryZone . '_PRICE']) {
            $result->setDeliveryPrice($this->config[$deliveryZone . '_PRICE']);

            if (!empty($this->config[$deliveryZone . '_FREE_FROM'])) {
                $order = $shipment->getParentOrder();
                if ($order->getPrice() >= $this->config[$deliveryZone . '_FREE_FROM']) {
                    $result->setDeliveryPrice(0);
                }
            }
        } else {
            $result->addError(new Error('Не задана стоимость доставки'));
        }

        /* @todo calculate delivery time */

        return $result;
    }
}
