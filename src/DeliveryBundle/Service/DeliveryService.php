<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;
use FourPaws\Location\LocationService;
use FourPaws\UserBundle\Service\UserService;

class DeliveryService
{
    const INNER_DELIVERY_CODE = '4lapy_delivery';

    const INNER_PICKUP_CODE = '4lapy_pickup';

    const ORDER_LOCATION_PROP_CODE = 'CITY_CODE';

    /**
     * @var LocationService $locationService
     */
    protected $locationService;

    /**
     * @var UserService $userService
     */
    protected $userService;

    public function __construct(LocationService $locationService, UserService $userService)
    {
        $this->locationService = $locationService;
        $this->userService = $userService;
    }

    /**
     * Получение доставок по id товара
     * 
     * @param $offerId
     * @param null $locationCode
     *
     * @return array
     */
    public function getByProduct(int $offerId, string $locationCode = null, $codes = []): array
    {
        if (!$locationCode) {
            $locationCode = $this->getCurrentLocation();
        }

        $shipment = $this->generateShipment($locationCode, $offerId);

        return $this->calculateDeliveries($shipment, $codes);
    }

    /**
     * Получение доставок для местоположения
     *
     * @param string $locationCode
     *
     * @return array
     */
    public function getByLocation(string $locationCode = null, $codes = []): array
    {
        if (!$locationCode) {
            $locationCode = $this->getCurrentLocation();
        }

        $shipment = $this->generateShipment($locationCode);

        return $this->calculateDeliveries($shipment, $codes);
    }

    /**
     * Выполняет расчет всех возможных (или указанных) доставок
     *
     * @param Shipment $shipment
     * @param array $codes коды доставок
     *
     * @return array
     */
    public function calculateDeliveries(Shipment $shipment, array $codes = [])
    {
        $availableServices = Manager::getRestrictedObjectsList($shipment);

        $result = [];
        foreach ($availableServices as $service) {
            if ($codes && !in_array($service->getCode(), $codes)) {
                continue;
            }

            if ($service->isProfile()) {
                $name = $service->getNameWithParent();
            } else {
                $name = $service->getName();
            }
            $service->getCode();
            $shipment->setFields(
                [
                    'DELIVERY_ID'   => $service->getId(),
                    'DELIVERY_NAME' => $name,
                ]
            );

            $calculationResult = $shipment->calculateDelivery($shipment);
            if ($calculationResult->isSuccess()) {
                $result[] = $calculationResult;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getCurrentLocation()
    {
        return $this->locationService->getCurrentLocation();
    }

    protected function generateShipment(string $locationCode, int $offerId = null): Shipment
    {
        $order = Order::create(
            SITE_ID,
            $this->userService->getAnonymousUserId(),
            CurrencyManager::getBaseCurrency()
        );

        $basket = Basket::createFromRequest([]);
        if ($offerId) {
            $basketItem = BasketItem::create($basket, 'sale', $offerId);
            /** todo нужна цена и кол-во товара */
            //            $basketItem->setFieldNoDemand('PRICE', $price);
            //            $basketItem->setFieldNoDemand('QUANTITY', $quantity);
            $basket->addItem($basketItem);
        }
        $order->setBasket($basket);

        $propertyCollection = $order->getPropertyCollection();
        $locationProp = $propertyCollection->getDeliveryLocation();
        $locationProp->setValue($locationCode);

        $shipmentCollection = $order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem();
        $shipmentItemCollection = $shipment->getShipmentItemCollection();
        $shipment->setField('CURRENCY', $order->getCurrency());

        /** @var BasketItem $item */
        foreach ($order->getBasket() as $item) {
            $shipmentItem = $shipmentItemCollection->createItem($item);
            $shipmentItem->setQuantity($item->getQuantity());
        }

        return $shipment;
    }
}
