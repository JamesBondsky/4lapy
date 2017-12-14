<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Main\Error;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService;

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
        $shops = $this->storeService->getByLocation($deliveryLocation, StoreService::TYPE_SHOP);
        if ($shops->isEmpty()) {
            return false;
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

        if (!$shopCode) {
            $result->addError(new Error('Не выбран пункт самовывоза'));

            return $result;
        }

        $deliveryLocation = $this->getDeliveryLocation($shipment);
        $shops = $this->storeService->getByLocation($deliveryLocation, StoreService::TYPE_SHOP);

        $shop = $shops->filter(
            function ($shop) use ($shopCode) {
                /** @var Store $shop */
                return $shop->getXmlId() == $shopCode;
            }
        )->first();

        if (!$shop) {
            $result->addError(new Error('Выбран неверный пункт самовывоза'));

            return $result;
        }

        $result->setDeliveryPrice(0);
        /* @todo учитывать наличие товара */
        $result->setPeriodFrom(1);
        $result->setPeriodType(CalculationResult::PERIOD_TYPE_HOUR);

        return $result;
    }
}
