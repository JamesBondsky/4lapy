<?php

namespace FourPaws\DeliveryBundle\Handler;

use Bitrix\Main\Error;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResult;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService;

class InnerPickupHandler extends DeliveryHandlerBase
{
    const ORDER_DELIVERY_PLACE_CODE_PROP = 'DELIVERY_PLACE_CODE';

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

        $deliveryLocation = $this->deliveryService->getDeliveryLocation($shipment);
        if (!$deliveryLocation) {
            return false;
        }

        $shops = $this->storeService->getByLocation($deliveryLocation, StoreService::TYPE_SHOP);
        if ($shops->isEmpty()) {
            return false;
        }

        return true;
    }

    public function getIntervals(Shipment $shipment): IntervalCollection
    {
        return new IntervalCollection();
    }

    protected function calculateConcrete(Shipment $shipment)
    {
        $result = new PickupResult();

        if (!$zone = $this->deliveryService->getDeliveryZoneCode($shipment)) {
            $result->addError(new Error('Не указано местоположение доставки'));
        } else {
            $result->setDeliveryZone($zone);
        }

        $deliveryLocation = $this->deliveryService->getDeliveryLocation($shipment);
        $basket = $shipment->getParentOrder()->getBasket()->getOrderableItems();

        $storesAll = $this->storeService->getByLocation($deliveryLocation, StoreService::TYPE_ALL);
        $shops = $storesAll->getShops();
        $stores = $storesAll->getStores();

        $shopCode = null;
        /* @var PropertyValue $prop */
        foreach ($shipment->getParentOrder()->getPropertyCollection() as $prop) {
            if ($prop->getField('CODE') == self::ORDER_DELIVERY_PLACE_CODE_PROP) {
                $shopCode = $prop->getValue();
                break;
            }
        }

        if ($shopCode) {
            /** @var StoreCollection $selectedShop */
            $shops = $shops->filter(
                function ($shop) use ($shopCode) {
                    /** @var Store $shop */
                    return $shop->getXmlId() == $shopCode;
                }
            );

            if ($shops->isEmpty()) {
                $result->addError(new Error('Выбран неверный пункт самовывоза'));

                return $result;
            }
        }

        if (!$offers = static::getOffers($deliveryLocation, $basket)) {
            /**
             * Нужно для отображения списка доставок в хедере и на странице доставок
             */
            return $result;
        }

        switch ($this->deliveryService->getDeliveryZoneCode($shipment)) {
            case DeliveryService::ZONE_1:
            case DeliveryService::ZONE_2:
            case DeliveryService::ZONE_3:
                /**
                 * условие доставки в эту зону - наличие в магазине
                 * условие отложенной доставки в эту зону - наличие на складе
                 */
                $delayStores = $stores;
                break;
            default:
                $result->addError(new Error('Доставка не работает для этой зоны'));

                return $result;
        }

        $stockResult = new StockResultCollection();
        /** @var Store $shop */
        foreach ($shops as $shop) {
            $availableStores = new StoreCollection([$shop]);
            $stockResult = static::getStocks($basket, $offers, $availableStores, $delayStores, $stockResult);
        }

        $result->setStockResult($stockResult);
        $result->setIntervals($this->getIntervals($shipment));

        if ($shopCode) {
            if (!$stockResult->getUnavailable()->isEmpty()) {
                $result->addError(new Error('Присутствуют товары не в наличии'));

                return $result;
            }
        }

        return $result;
    }
}
