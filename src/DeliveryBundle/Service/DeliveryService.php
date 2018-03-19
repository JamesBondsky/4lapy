<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Service;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\Table as DeliveryServiceTable;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Dpd\TerminalTable;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DpdPickupResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DpdResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use WebArch\BitrixCache\BitrixCache;

class DeliveryService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const INNER_DELIVERY_CODE = '4lapy_delivery';

    public const INNER_PICKUP_CODE = '4lapy_pickup';

    public const DPD_DELIVERY_GROUP_CODE = 'ipolh_dpd';

    public const DPD_DELIVERY_CODE = self::DPD_DELIVERY_GROUP_CODE . ':COURIER';

    public const DPD_PICKUP_CODE = self::DPD_DELIVERY_GROUP_CODE . ':PICKUP';

    public const ORDER_LOCATION_PROP_CODE = 'CITY_CODE';

    public const LOCATION_RESTRICTION_TYPE_LOCATION = 'L';

    public const LOCATION_RESTRICTION_TYPE_GROUP = 'G';

    public const ZONE_1 = 'ZONE_1';

    public const ZONE_2 = 'ZONE_2';

    public const ZONE_3 = 'ZONE_3';

    public const ZONE_4 = 'ZONE_4';

    public const PICKUP_CODES = [
        DeliveryService::INNER_PICKUP_CODE,
        DeliveryService::DPD_PICKUP_CODE,
    ];

    public const DELIVERY_CODES = [
        DeliveryService::INNER_DELIVERY_CODE,
        DeliveryService::DPD_DELIVERY_CODE,
    ];

    /**
     * @var LocationService $locationService
     */
    protected $locationService;

    /**
     * DeliveryService public constructor.
     *
     * @param LocationService $locationService
     */
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
        $this->setLogger(LoggerFactory::create('DeliveryService'));
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Получение доставок для товара
     *
     * @param Offer $offer
     * @param string $locationCode
     * @param array $codes
     * @param \DateTime|null $from
     * @return array
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Sale\UserMessageException
     */
    public function getByProduct(
        Offer $offer,
        string $locationCode = '',
        array $codes = [],
        ?\DateTime $from = null
    ): array {
        $basket = Basket::createFromRequest([]);
        $basketItem = BasketItem::create($basket, 'sale', $offer->getId());
        $basketItem->setFieldNoDemand('CAN_BUY', 'Y');
        $basketItem->setFieldNoDemand('PRICE', $offer->getPrice());
        $basketItem->setFieldNoDemand('QUANTITY', 1);
        $basket->addItem($basketItem);

        return $this->getByBasket($basket, $locationCode, $codes, $from);
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Получение доставок для корзины
     *
     * @param BasketBase $basket
     * @param string $locationCode
     * @param array $codes
     * @param \DateTime|null $from
     * @return array
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotSupportedException
     */
    public function getByBasket(
        BasketBase $basket,
        string $locationCode = '',
        array $codes = [],
        ?\DateTime $from = null
    ): array {
        if (!$locationCode) {
            $locationCode = $this->locationService->getCurrentLocation();
        }

        $shipment = $this->generateShipment($locationCode, $basket);

        return $this->calculateDeliveries($shipment, $codes, $from);
    }

    /**
     * Получение доставок для местоположения
     *
     * @param string $locationCode
     * @param array $codes
     * @return array
     */
    public function getByLocation(string $locationCode, array $codes = []): array
    {
        $deliveries = [];
        $getDeliveries = function () use ($locationCode) {
            $shipment = $this->generateShipment($locationCode);

            return ['result' => $this->calculateDeliveries($shipment)];
        };

        try {
            $result = (new BitrixCache())
                ->withId(__METHOD__ . $locationCode)
                ->resultOf($getDeliveries);
            $deliveries = $result['result'];
        } catch (\Exception $e) {
            $this->logger->error('failed to get deliveries for location', ['locationCode' => $locationCode]);
        }
        if (!empty($codes)) {
            /**
             * @var CalculationResultInterface $delivery
             */
            foreach ($deliveries as $i => $delivery) {
                if (!\in_array($delivery->getDeliveryCode(), $codes, true)) {
                    unset($deliveries[$i]);
                }
            }
        }

        return $deliveries;
    }

    /**
     * Выполняет расчет всех возможных (или указанных) доставок
     *
     * @param Shipment $shipment
     * @param array $codes
     * @param \DateTime|null $from
     * @return array
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotSupportedException
     */
    public function calculateDeliveries(Shipment $shipment, array $codes = [], ?\DateTime $from = null): array
    {
        $availableServices = Manager::getRestrictedObjectsList($shipment);

        $result = [];

        foreach ($availableServices as $service) {
            if ($codes && !\in_array($service->getCode(), $codes, true)) {
                continue;
            }

            $isDpd = \in_array(
                $service->getCode(),
                [
                    self::DPD_DELIVERY_CODE,
                    self::DPD_PICKUP_CODE,
                ],
                true
            );

            if ($service::isProfile()) {
                $name = $service->getNameWithParent();
            } else {
                $name = $service->getName();
            }
            $service->getCode();
            $shipment->setFields(
                [
                    'DELIVERY_ID' => $service->getId(),
                    'DELIVERY_NAME' => $name,
                ]
            );
            $calculationResult = $shipment->calculateDelivery();
            $from = $from ?? new \DateTime();

            if ($isDpd) {
                if ($service->getCode() === static::DPD_PICKUP_CODE) {
                    $calculationResult = new DpdPickupResult($calculationResult);
                    $calculationResult->setTerminals($_SESSION['DPD_DATA'][$service->getCode()]['TERMINALS']);
                } else {
                    $calculationResult = new DpdResult($calculationResult);
                }
                $calculationResult->setDeliveryCode($service->getCode());
                /* @todo не хранить эти данные в сессии */
                $calculationResult->setInitialPeriod($_SESSION['DPD_DATA'][$service->getCode()]['DAYS_FROM']);
                $calculationResult->setPeriodTo($_SESSION['DPD_DATA'][$service->getCode()]['DAYS_TO']);
                if ($_SESSION['DPD_DATA'][$service->getCode()]['STOCK_RESULT'] instanceof StockResultCollection) {
                    $calculationResult->setStockResult($_SESSION['DPD_DATA'][$service->getCode()]['STOCK_RESULT']);
                }
                $calculationResult->setIntervals($_SESSION['DPD_DATA'][$service->getCode()]['INTERVALS']);
                $calculationResult->setDeliveryZone($_SESSION['DPD_DATA'][$service->getCode()]['DELIVERY_ZONE']);
                unset($_SESSION['DPD_DATA']);
            }
            if (!$calculationResult instanceof CalculationResultInterface) {
                // непонятная доставка, мы с такими работать не обучены
                continue;
            }
            $calculationResult->setDeliveryId($service->getId());
            $calculationResult->setDeliveryName($name);
            $calculationResult->setDeliveryCode($service->getCode());
            $calculationResult->setCurrentDate($from);

            if ($calculationResult->isSuccess()) {
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
        $allZones = $this->getAllZones();

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

                while ($location = $locations->fetch()) {
                    // сделано, чтобы отдельные местоположения были впереди групп,
                    // т.к. группы могут их включать
                    $result = [
                            $location['CODE'] => [
                                'CODE' => $location['CODE'],
                                'NAME' => $location['SALE_LOCATION_LOCATION_NAME_NAME'],
                                'ID' => $location['ID'],
                                'LOCATIONS' => [$location['CODE']],
                                'TYPE' => static::LOCATION_RESTRICTION_TYPE_LOCATION,
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
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isPickup(CalculationResultInterface $calculationResult): bool
    {
        return \in_array($calculationResult->getDeliveryCode(), static::PICKUP_CODES, true);
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isDelivery(CalculationResultInterface $calculationResult): bool
    {
        return \in_array($calculationResult->getDeliveryCode(), static::DELIVERY_CODES, true);
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isInnerPickup(CalculationResultInterface $calculationResult): bool
    {
        return $calculationResult->getDeliveryCode() === static::INNER_PICKUP_CODE;
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isDpdPickup(CalculationResultInterface $calculationResult): bool
    {
        return $calculationResult->getDeliveryCode() === static::DPD_PICKUP_CODE;
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isInnerDelivery(CalculationResultInterface $calculationResult): bool
    {
        return $calculationResult->getDeliveryCode() === static::INNER_DELIVERY_CODE;
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isDpdDelivery(CalculationResultInterface $calculationResult): bool
    {
        return $calculationResult->getDeliveryCode() === static::DPD_DELIVERY_CODE;
    }

    /**
     * @param string $code
     *
     * @throws NotFoundException
     * @return int
     */
    public function getDeliveryIdByCode(string $code): int
    {
        return (int)$this->getDeliveryByCode($code)['ID'];
    }

    /**
     * @param string $code
     *
     * @throws NotFoundException
     * @return array
     */
    public function getDeliveryByCode(string $code): array
    {
        $delivery = DeliveryServiceTable::getList(['filter' => ['CODE' => $code]])->fetch();
        if (!$delivery) {
            throw new NotFoundException('Delivery service not found');
        }

        return $delivery;
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     * @return string
     */
    public function getDeliveryCodeById(int $id): string
    {
        $delivery = DeliveryServiceTable::getList(['filter' => ['ID' => $id]])->fetch();
        if (!$delivery) {
            throw new NotFoundException('Delivery service not found');
        }

        return $delivery['CODE'];
    }

    /**
     * Получение терминалов DPD
     *
     * @param string $locationCode код местоположения
     * @param bool $withCod только те, где возможен наложенный платеж
     * @param float $sum сумма наложенного платежа
     *
     * @return StoreCollection
     */
    public function getDpdTerminalsByLocation(
        string $locationCode,
        bool $withCod = true,
        float $sum = 0
    ): StoreCollection {
        $result = new StoreCollection();

        $getTerminals = function () use ($locationCode) {
            $terminals = TerminalTable::query()
                ->setSelect(['*'])
                ->setFilter(['LOCATION.CODE' => $locationCode])
                ->registerRuntimeField(
                    new ReferenceField(
                        'LOCATION',
                        LocationTable::class,
                        ['=this.LOCATION_ID' => 'ref.ID'],
                        ['join_type' => 'INNER']
                    )
                )
                ->exec();

            return ['result' => $terminals->fetchAll()];
        };

        /** @var array $terminals */
        $terminals = (new BitrixCache())
            ->withId(__METHOD__ . $locationCode)
            ->resultOf($getTerminals)['result'];

        if ($withCod) {
            $terminals = array_filter(
                $terminals,
                function ($item) use ($sum) {
                    return ($item['NPP_AVAILABLE'] === 'Y') && ($item['NPP_AMOUNT'] >= $sum);
                }
            );
        }

        foreach ($terminals as $terminal) {
            $store = $this->dpdTerminalToStore($terminal, $locationCode);
            $result[$store->getXmlId()] = $store;
        }

        return $result;
    }

    /**
     * @param $code
     *
     * @throws NotFoundException
     * @return Store
     */
    public function getDpdTerminalByCode($code): Store
    {
        $getTerminal = function () use ($code) {
            $terminal = TerminalTable::query()->setSelect(['*', 'LOCATION.CODE'])
                ->setFilter(['CODE' => $code])
                ->registerRuntimeField(
                    new ReferenceField(
                        'LOCATION',
                        LocationTable::class,
                        ['=this.LOCATION_ID' => 'ref.ID'],
                        ['join_type' => 'INNER']
                    )
                )
                ->exec()->fetch();
            if (!$terminal) {
                throw new NotFoundException('Терминал не найден');
            }

            return ['result' => $terminal];
        };

        /** @var array $terminals */
        $terminal = (new BitrixCache())
            ->withId(__METHOD__ . $code)
            ->resultOf($getTerminal)['result'];

        return $this->dpdTerminalToStore($terminal, $terminal['FOURPAWS_DELIVERYBUNDLE_DPD_TERMINAL_LOCATION_CODE']);
    }

    protected function generateShipment(string $locationCode, BasketBase $basket = null): Shipment
    {
        $order = Order::create(
            SITE_ID,
            null,
            CurrencyManager::getBaseCurrency()
        );

        $order->setMathActionOnly(true);

        if (!$basket) {
            $basket = Basket::createFromRequest([]);
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

    /**
     * @param array $terminal
     * @param string $locationCode
     * @return Store
     */
    protected function dpdTerminalToStore(array $terminal, string $locationCode = ''): Store
    {
        $schedule = str_replace('<br>', '. ', $terminal['SCHEDULE_SELF_DELIVERY']);

        $store = new Store();
        $store->setTitle((string)$terminal['NAME'])
            ->setLocation($locationCode)
            ->setAddress((string)$terminal['ADDRESS_SHORT'])
            ->setCode((string)$terminal['CODE'])
            ->setXmlId((string)$terminal['CODE'])
            ->setLatitude((float)$terminal['LATITUDE'])
            ->setLongitude((float)$terminal['LONGITUDE'])
            ->setLocationId((int)$terminal['LOCATION_ID'])
            ->setScheduleString($schedule)
            ->setDescription((string)$terminal['ADDRESS_DESCR']);

        return $store;
    }
}
