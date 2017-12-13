<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Main\Error;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;

class InnerPickupService extends DeliveryServiceBase
{
    protected $code = '4lapy_pickup';

    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
    }

    public static function getClassTitle()
    {
        return 'Самовывоз из магазина "Четыре лапы"';
    }

    public static function getClassDescription()
    {
        return 'Обработчик самовывоза "Четыре лапы"';
    }

    public function isCompatible(Shipment $shipment)
    {
        if (!parent::isCompatible($shipment)) {
            return false;
        }

        $deliveryLocation = $this->getDeliveryLocation($shipment);
        if (!$shopCodes = $this->locationService->getShopsByCity($deliveryLocation)) {
            //            return false; // @todo раскомментировать, когда можно будет получить список магазинов
        }

        /** @todo проверка остатков товаров */

        return true;
    }

    public function getIntervals(Shipment $shipment): array
    {
        return [];
    }

    protected function calculateConcrete(Shipment $shipment)
    {
        $result = parent::calculateConcrete($shipment);
        if (!$result->isSuccess()) {
            return $result;
        }

        $order = $shipment->getParentOrder();
        $propertyCollection = $order->getPropertyCollection();

        $shopCode = null;
        /* @var PropertyValue $prop */
        foreach ($propertyCollection as $prop) {
            if ($prop->getField('CODE') == 'DELIVERY_PLACE_CODE') {
                $shopCode = $prop->getValue();
                break;
            }
        }

        $deliveryLocation = $this->getDeliveryLocation($shipment);
        $shopCodes = $this->locationService->getShopsByCity($deliveryLocation);

        if (!$shopCode) {
            $result->addError(new Error('Не выбран пункт самовывоза'));
        } elseif (!in_array($shopCode, $shopCodes)) {
            $result->addError(new Error('Выбран неверный пункт самовывоза'));
        }

        $result = new \Bitrix\Sale\Delivery\CalculationResult();
        $result->setDeliveryPrice(0);
        /* @todo учитывать наличие товара */
        $result->setPeriodFrom(1);
        $result->setPeriodType(CalculationResult::PERIOD_TYPE_HOUR);

        return $result;
    }
}
