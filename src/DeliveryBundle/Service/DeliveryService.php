<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\Table as DeliveryServiceTable;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Dpd\TerminalTable;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Exception\UnknownDeliveryException;
use FourPaws\DeliveryBundle\Factory\CalculationResultFactory;
use FourPaws\LocationBundle\LocationService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use Psr\Log\LoggerAwareInterface;
use WebArch\BitrixCache\BitrixCache;

class DeliveryService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

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

    /** @var array */
    public static $dpdData = [];

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
        $this->withLogName('DeliveryService');
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Получение доставок для товара
     *
     * @param Offer $offer
     * @param string $locationCode
     * @param array $codes
     * @param \DateTime|null $from
     *
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws LoaderException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     * @throws UserMessageException
     * @return CalculationResultInterface[]
     */
    public function getByProduct(
        Offer $offer,
        string $locationCode = '',
        array $codes = [],
        ?\DateTime $from = null
    ): array {
        $basket = Basket::createFromRequest([]);
        $basketItem = BasketItem::create($basket, 'sale', $offer->getId());
        /** @noinspection PhpInternalEntityUsedInspection */
        $basketItem->setFieldNoDemand('CAN_BUY', 'Y');
        /** @noinspection PhpInternalEntityUsedInspection */
        $basketItem->setFieldNoDemand('PRICE', $offer->getPrice());
        /** @noinspection PhpInternalEntityUsedInspection */
        $basketItem->setFieldNoDemand('QUANTITY', 1);
        /** @noinspection PhpInternalEntityUsedInspection */
        $basket->addItem($basketItem);

        return $this->getByBasket($basket, $locationCode, $codes, $from);
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Получение доставок для корзины
     * @param BasketBase $basket
     * @param string $locationCode
     * @param array $codes
     * @param \DateTime|null $from
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     * @throws UserMessageException
     * @return array
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
     * @return CalculationResultInterface[]
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
            $this->log()->error(sprintf('failed to get deliveries for location: %s', $e->getMessage()), [
                'location' => $locationCode
            ]);
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
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     * @return CalculationResultInterface[]
     */
    public function calculateDeliveries(Shipment $shipment, array $codes = [], ?\DateTime $from = null): array
    {
        $availableServices = Manager::getRestrictedObjectsList($shipment);

        $result = [];

        foreach ($availableServices as $service) {
            if ($codes && !\in_array($service->getCode(), $codes, true)) {
                continue;
            }

            if ($service::isProfile()) {
                $name = $service->getNameWithParent();
            } else {
                $name = $service->getName();
            }
            /**
             * todo раскомментировать строчки, выключающие постобработку кастомных акций, либо расчитывать как-то по-другому
             */
            //\FourPaws\SaleBundle\Discount\Utils\Manager::disableProcessingFinalAction();
            try {
                $shipment->setFields(
                    [
                        'DELIVERY_ID' => $service->getId(),
                        'DELIVERY_NAME' => $name,
                    ]
                );
            } catch (\Exception $e) {
                $this->log()->error(sprintf('Cannot set shipment fields: %s', $e->getMessage()), [
                    'location' => $this->getDeliveryLocation($shipment),
                    'service' => $service->getCode()
                ]);
                continue;
            }
            //\FourPaws\SaleBundle\Discount\Utils\Manager::enableProcessingFinalAction();
            $calculationResult = $shipment->calculateDelivery();
            if (!$calculationResult->isSuccess()) {
                continue;
            }

            try {
                $calculationResult = CalculationResultFactory::fromBitrixResult($calculationResult, $service);
            } catch (UnknownDeliveryException $e) {
                $this->log()->critical($e->getMessage(), [
                    'service' => $service->getCode(),
                    'location' => $this->getDeliveryLocation($shipment)
                ]);
                continue;
            }
            $calculationResult->setDeliveryZone($this->getDeliveryZoneCode($shipment));
            $calculationResult->setDeliveryId($service->getId());
            $calculationResult->setDeliveryName($name);
            $calculationResult->setDeliveryCode($service->getCode());
            $calculationResult->setCurrentDate($from ?? new \DateTime());

            if ($calculationResult->isSuccess()) {
                $result[] = $calculationResult;
            }
        }

        return $result;
    }

    /**
     * Получить все зоны доставки
     *
     * @param bool $withLocations
     * @return array
     */
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
     * @throws ObjectNotFoundException
     */
    public function getDeliveryLocation(Shipment $shipment): ?string
    {
        /** @noinspection PhpInternalEntityUsedInspection */
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
     *
     * @param bool $skipLocations
     * @throws ObjectNotFoundException
     * @return null|string
     */
    public function getDeliveryZoneCode(Shipment $shipment, $skipLocations = true): ?string
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
     * @return null|string
     */
    public function getDeliveryZoneCodeByLocation($deliveryLocation, $deliveryId, $skipLocations = true): ?string
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

        return null;
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

        try {
            $result = (new BitrixCache())
                ->withId(__METHOD__ . $deliveryId)
                ->resultOf($getZones);
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to get available zones: %s', $e->getMessage()), [
                'deliveryId' => $deliveryId
            ]);
            return [];
        }

        return $result;
    }

    /**
     * @param string|null $deliveryCode
     * @return bool
     */
    public function isPickupCode($deliveryCode): bool
    {
        return $deliveryCode && \in_array($deliveryCode, static::PICKUP_CODES, true);
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isPickup(CalculationResultInterface $calculationResult): bool
    {
        return $this->isPickupCode($calculationResult->getDeliveryCode());
    }

    /**
     * @param string|null $deliveryCode
     * @return bool
     */
    public function isDeliveryCode($deliveryCode): bool
    {
        return $deliveryCode && \in_array($deliveryCode, static::DELIVERY_CODES, true);
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isDelivery(CalculationResultInterface $calculationResult): bool
    {
        return $this->isDeliveryCode($calculationResult->getDeliveryCode());
    }

    /**
     * @param string|null $deliveryCode
     * @return bool
     */
    public function isInnerPickupCode($deliveryCode): bool
    {
        return $deliveryCode && $deliveryCode === static::INNER_PICKUP_CODE;
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isInnerPickup(CalculationResultInterface $calculationResult): bool
    {
        return $this->isInnerPickupCode($calculationResult->getDeliveryCode());
    }

    /**
     * @param string|null $deliveryCode
     * @return bool
     */
    public function isDpdPickupCode($deliveryCode): bool
    {
        return $deliveryCode && $deliveryCode === static::DPD_PICKUP_CODE;
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isDpdPickup(CalculationResultInterface $calculationResult): bool
    {
        return $this->isDpdPickupCode($calculationResult->getDeliveryCode());
    }

    /**
     * @param string|null $deliveryCode
     * @return bool
     */
    public function isInnerDeliveryCode($deliveryCode): bool
    {
        return $deliveryCode && $deliveryCode === static::INNER_DELIVERY_CODE;
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isInnerDelivery(CalculationResultInterface $calculationResult): bool
    {
        return $this->isInnerDeliveryCode($calculationResult->getDeliveryCode());
    }

    /**
     * @param string|null $deliveryCode
     * @return bool
     */
    public function isDpdDeliveryCode($deliveryCode): bool
    {
        return $deliveryCode && $deliveryCode === static::DPD_DELIVERY_CODE;
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isDpdDelivery(CalculationResultInterface $calculationResult): bool
    {
        return $this->isDpdDeliveryCode($calculationResult->getDeliveryCode());
    }

    /**
     * @param string $code
     *
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @return int
     */
    public function getDeliveryIdByCode(string $code): int
    {
        return (int)$this->getDeliveryByCode($code)['ID'];
    }

    /**
     * @param string $code
     *
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
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
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
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
            /** @noinspection PhpUndefinedClassInspection */
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

        try {
            /** @var array $terminals */
            $terminals = (new BitrixCache())
                ->withId(__METHOD__ . $locationCode)
                ->resultOf($getTerminals)['result'];
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to get dpd terminals: %s', $e->getMessage()), [
                'location' => $locationCode
            ]);
            return $result;
        }

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
     * @return Store|null
     */
    public function getDpdTerminalByCode($code): ?Store
    {
        $getTerminal = function () use ($code) {
            /** @noinspection PhpUndefinedClassInspection */
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

        try {
            $terminal = (new BitrixCache())
                ->withId(__METHOD__ . $code)
                ->resultOf($getTerminal)['result'];
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to get dpd terminal: %s', $e->getMessage()), [
                'code' => $code
            ]);
            return null;
        }

        return $this->dpdTerminalToStore($terminal, $terminal['FOURPAWS_DELIVERYBUNDLE_DPD_TERMINAL_LOCATION_CODE']);
    }

    /**
     * @param string $locationCode
     * @param BasketBase|null $basket
     * @return Shipment
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws UserMessageException
     */
    protected function generateShipment(string $locationCode, BasketBase $basket = null): Shipment
    {
        $order = Order::create(
            SITE_ID,
            null,
            CurrencyManager::getBaseCurrency()
        );

        /** @noinspection PhpInternalEntityUsedInspection */
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
        try {
            $shipment->setField('CURRENCY', $order->getCurrency());
        } catch (\Exception $e) {
            $this->log()->error(sprintf('Failed to set shipment currency: %s', $e->getMessage()), [
                'location' => $locationCode
            ]);
        }

        try {
            /** @var BasketItem $item */
            foreach ($order->getBasket() as $item) {
                $shipmentItem = $shipmentItemCollection->createItem($item);
                $shipmentItem->setQuantity($item->getQuantity());
            }
        } catch (\Exception $e) {
            $this->log()->error(sprintf('Failed to set shipmentItem quantity: %s', $e->getMessage()), [
                'location' => $locationCode
            ]);
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
