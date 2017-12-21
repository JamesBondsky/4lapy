<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;
use FourPaws\Location\LocationService;
use FourPaws\UserBundle\Service\UserService;
use WebArch\BitrixCache\BitrixCache;

class DeliveryService
{
    const INNER_DELIVERY_CODE = '4lapy_delivery';

    const INNER_PICKUP_CODE = '4lapy_pickup';

    const DPD_DELIVERY_GROUP_CODE = 'ipolh_dpd';

    const DPD_DELIVERY_CODE = 'ipolh_dpd:COURIER';

    const DPD_PICKUP_CODE = 'ipolh_dpd:PICKUP';

    const ORDER_LOCATION_PROP_CODE = 'CITY_CODE';

    const LOCATION_RESTRICTION_TYPE_LOCATION = 'L';

    const LOCATION_RESTRICTION_TYPE_GROUP = 'G';

    const ZONE_1 = 'ZONE_1';

    const ZONE_2 = 'ZONE_2';

    const ZONE_3 = 'ZONE_3';

    const ZONE_4 = 'ZONE_4';

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
     * @param int $offerId
     * @param string $locationCode
     * @param array $codes коды доставок для расчета
     *
     * @return array
     */
    public function getByProduct(int $offerId, string $locationCode = null, array $codes = []): array
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
     * @param array $codes коды доставок для расчета
     *
     * @return array
     */
    public function getByLocation(string $locationCode = null, array $codes = []): array
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

            $calculationResult = $shipment->calculateDelivery();
            if ($calculationResult->isSuccess()) {
                if (in_array(
                    $service->getCode(),
                    [
                        DeliveryService::DPD_DELIVERY_CODE,
                        DeliveryService::DPD_PICKUP_CODE,
                    ],
                    true
                )) {
                    $calculationResult->setPeriodFrom($_SESSION['DPD_DATA'][$service->getCode()]['DPD_TARIFF']['DAYS']);
                    $calculationResult->setData(
                        array_merge(
                            $calculationResult->getData(),
                            [
                                'INTERVALS' => $_SESSION['DPD_DATA'][$service->getCode()]['INTERVALS'],
                            ]
                        )
                    );
                }

                $calculationResult->setData(
                    array_merge(
                        [
                            'DELIVERY_ID'   => $service->getId(),
                            'DELIVERY_NAME' => $name,
                            'DELIVERY_CODE' => $service->getCode(),
                        ],
                        $calculationResult->getData()
                    )
                );

                $result[] = $calculationResult;
            }
        }

        return $result;
    }

    public function getAllZones($withLocations = true): array
    {
        return $this->locationService->getLocationGroups($withLocations);
    }

    /**
     * Получение кода местоположения для доставки
     *
     * @param Shipment $shipment
     *
     * @return null|string
     */
    public function getDeliveryLocation(Shipment $shipment)
    {
        $order = $shipment->getParentOrder();
        $propertyCollection = $order->getPropertyCollection();
        $locationProp = $propertyCollection->getDeliveryLocation();

        if ($locationProp && $locationProp->getValue()) {
            return $locationProp->getValue();
        }

        return null;
    }

    /**
     * Получение кода зоны доставки. Содержит либо код группы доставки,
     * либо код местоположения (в случае, если в ограничениях указано
     * отдельное местоположение)
     *
     * @param Shipment $shipment
     * @param bool $skipLocations возвращать только коды групп
     *
     * @return bool|string
     */
    public function getDeliveryZoneCode(Shipment $shipment, $skipLocations = true)
    {
        if (!$deliveryLocation = $this->getDeliveryLocation($shipment)) {
            return false;
        }
        $deliveryId = $shipment->getDeliveryId();

        return $this->getDeliveryZoneCodeByLocation($deliveryLocation, $deliveryId, $skipLocations);
    }

    /**
     * @param $deliveryLocation
     * @param $deliveryId
     * @param bool $skipLocations
     *
     * @return bool|int|string
     */
    public function getDeliveryZoneCodeByLocation($deliveryLocation, $deliveryId, $skipLocations = true)
    {
        $deliveryLocationPath = [$deliveryLocation];
        if (($location = $this->locationService->findLocationByCode($deliveryLocation)) && $location['PATH']) {
            $deliveryLocationPath = array_merge(
                $deliveryLocationPath,
                array_column($location['PATH'], 'CODE')
            );
        }

        $availableZones = $this->getAvailableZones($deliveryId);

        foreach ($availableZones as $code => $zone) {
            if ($skipLocations && $zone['TYPE'] === static::LOCATION_RESTRICTION_TYPE_LOCATION) {
                continue;
            }
            if (!empty(array_intersect($deliveryLocationPath, $zone['LOCATIONS']))) {
                return $code;
            }
        }

        return false;
    }

    /**
     * Получение доступных зон доставки в соответствии с ограничениями по местоположению
     *
     * @param int $deliveryId
     *
     * @return array
     */
    public function getAvailableZones(int $deliveryId): array
    {
        $allZones = $this->getAllZones(true);

        $getZones = function () use ($allZones, $deliveryId) {
            $result = [];

            $restrictions = DeliveryLocationTable::getList(
                [
                    'filter' => ['DELIVERY_ID' => $deliveryId],
                ]
            );

            $locationCodes = [];
            while ($restriction = $restrictions->fetch()) {
                switch ($restriction['LOCATION_TYPE']) {
                    case static::LOCATION_RESTRICTION_TYPE_LOCATION:
                        $locationCodes[] = $restriction['LOCATION_CODE'];
                        break;
                    case static::LOCATION_RESTRICTION_TYPE_GROUP:
                        if (isset($allZones[$restriction['LOCATION_CODE']])) {
                            $item = $allZones[$restriction['LOCATION_CODE']];
                            $item['TYPE'] = static::LOCATION_RESTRICTION_TYPE_GROUP;
                            $result[$restriction['LOCATION_CODE']] = $item;
                        }
                        break;
                }
            }

            if (!empty($locationCodes)) {
                $locations = LocationTable::getList(
                    [
                        'filter' => ['CODE' => $locationCodes],
                        'select' => ['ID', 'CODE', 'NAME.NAME'],
                    ]
                );

                while ($location = $locations->Fetch()) {
                    // сделано, чтобы отдельные местоположения были впереди групп,
                    // т.к. группы могут их включать
                    $result = [
                            $location['CODE'] => [
                                'CODE'      => $location['CODE'],
                                'NAME'      => $location['SALE_LOCATION_LOCATION_NAME_NAME'],
                                'ID'        => $location['ID'],
                                'LOCATIONS' => [$location['CODE']],
                                'TYPE'      => static::LOCATION_RESTRICTION_TYPE_LOCATION,
                            ],
                        ] + $result;
                }
            }

            return $result;
        };

        $result = (new BitrixCache())
            ->withId(__METHOD__ . $deliveryId)
            ->resultOf($getZones);

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
