<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Service;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use FourPaws\Adapter\DaDataLocationAdapter;
use FourPaws\Adapter\Model\Output\BitrixLocation;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Repository\StoreRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use WebArch\BitrixCache\BitrixCache;

class StoreService implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * Все склады, исключая склады поставщиков
     */
    public const TYPE_ALL = 'TYPE_ALL';

    /**
     * Все склады
     */
    public const TYPE_ALL_WITH_SUPPLIERS = 'TYPE_ALL_WITH_SUPPLIERS';

    /**
     * Склады, не являющиеся магазинами, исключая склады поставщиков
     */
    public const TYPE_STORE = 'TYPE_STORE';

    /**
     * Склады, являющиеся магазинами
     */
    public const TYPE_SHOP = 'TYPE_SHOP';

    /**
     * Склады поставщиков
     */
    public const TYPE_SUPPLIER = 'TYPE_SUPPLIER';

    /**
     * Базовые магазины
     */
    public const TYPE_BASE_SHOP = 'TYPE_BASE_SHOP';

    /**
     * @var LocationService
     */
    protected $locationService;

    /**
     * @var StoreRepository
     */
    protected $storeRepository;

    /** @var DeliveryService $deliveryService */
    protected $deliveryService;

    /** @var array */
    protected $stores = [];

    public function __construct(
        LocationService $locationService,
        StoreRepository $storeRepository,
        DeliveryService $deliveryService
    ) {
        $this->locationService = $locationService;
        $this->storeRepository = $storeRepository;
        $this->deliveryService = $deliveryService;
        $this->setLogger(LoggerFactory::create('StoreService'));
    }


    /**
     * @param string $type
     * @param array  $filter
     * @param array  $order
     *
     * @return StoreCollection
     * @throws ArgumentException
     */
    public function getStores(
        string $type = self::TYPE_ALL,
        array $filter = [],
        array $order = []
    ): StoreCollection {
        $filter = \array_merge($this->getTypeFilter($type), $filter);
        return $this->storeRepository->findBy($filter, $order);
    }

    /**
     * Получить склад по ID
     *
     * @param int $id
     *
     * @throws NotFoundException
     * @return Store
     */
    public function getStoreById(int $id): Store
    {
        $store = null;

        $getStore = function () use ($id) {
            return ['result' => $this->storeRepository->find($id)];
        };

        try {
            $store = (new BitrixCache())
                ->withId(__METHOD__ . $id)
                ->withTag('catalog:store')
                ->resultOf($getStore)['result'];
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('failed to get store with id %s: %s', $id, $e->getMessage())
            );
        }

        if (!$store || !$store instanceof Store) {
            throw new NotFoundException('Склад с ID=' . $id . ' не найден');
        }

        return $store;
    }

    /**
     * Получить склад по XML_ID
     *
     * @param $xmlId
     *
     * @throws NotFoundException
     * @return Store
     */
    public function getStoreByXmlId($xmlId): Store
    {
        if (!isset($this->stores[$xmlId])) {
            $getStore = function () use ($xmlId) {
                $store = $this->storeRepository->findBy(
                    [
                        'XML_ID' => $xmlId,
                        [],
                        1,
                    ]
                )->first();

                return ['result' => $store];
            };

            $store = null;
            try {
                $store = (new BitrixCache())
                    ->withId(__METHOD__ . $xmlId)
                    ->withTag('catalog:store')
                    ->resultOf($getStore)['result'];
            } catch (\Exception $e) {
                $this->logger->error(
                    sprintf('failed to get store by xmlId: %s: %s', \get_class($e), $e->getMessage()),
                    ['xmlId' => $xmlId]
                );
            }
            if (!$store) {
                throw new NotFoundException('Склад с XML_ID=' . $xmlId . ' не найден');
            }

            $this->stores[$xmlId] = $store;
        }

        return $this->stores[$xmlId];
    }

    /**
     * Получить склады в текущем местоположении
     *
     * @param string $type
     *
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @return StoreCollection
     */
    public function getStoresByCurrentLocation($type = self::TYPE_ALL): StoreCollection
    {
        $location = $this->locationService->getCurrentLocation();
        if ($this->deliveryService->getCurrentDeliveryZone() === DeliveryService::ZONE_4) {
            $type = self::TYPE_STORE;
        }

        return $this->getStoresByLocation($location, $type);
    }

    /**
     * Получить склады, привязанные к указанному местоположению
     *
     * @param string $locationCode
     * @param string $type
     * @param bool   $strict
     *
     * @throws ArgumentException
     * @return StoreCollection
     */
    public function getStoresByLocation(
        string $locationCode,
        string $type = self::TYPE_ALL,
        bool $strict = false
    ): StoreCollection {
        $getStores = function () use ($locationCode, $type) {
            $storeCollection = $this->getStores($type, ['UF_LOCATION' => $locationCode]);

            return ['result' => $storeCollection];
        };

        try {
            $result = (new BitrixCache())
                ->withId(__METHOD__ . $locationCode . $type)
                ->withTag('catalog:store')
                ->resultOf($getStores);

            /** @var StoreCollection $stores */
            $stores = $result['result'];
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf(
                    'failed to get stores for location: %s',
                    $e->getMessage()
                ),
                ['location' => $locationCode, 'type' => $type]
            );
            $stores = new StoreCollection();
        }

        /**
         * Ищем склады района и региона
         */
        if (!$strict && $stores->isEmpty()) {
            $stores = $this->getSubRegionalStores($locationCode, $type);
            if ($stores->isEmpty()) {
                $stores = $this->getRegionalStores($locationCode, $type);
            }
        }

        /**
         * Если не нашлось ничего с типом "склад" для данного местоположения, то добавляем склады для Москвы
         */
        if (!$strict &&
            $locationCode !== LocationService::LOCATION_CODE_MOSCOW &&
            \in_array($type, [self::TYPE_STORE, self::TYPE_ALL], true) &&
            $stores->getStores()->isEmpty()
        ) {
            $moscowStores = $this->getStoresByLocation(LocationService::LOCATION_CODE_MOSCOW, self::TYPE_STORE);
            $stores = new StoreCollection(array_merge($stores->toArray(), $moscowStores->toArray()));
        }

        return $stores;
    }

    /**
     * @param string $locationCode
     * @return StoreCollection
     */
    public function getBaseShops(string $locationCode): StoreCollection
    {
        $getStores = function () use ($locationCode) {
            return [
                'result' => $this->getStores(
                    static::TYPE_BASE_SHOP,
                    [
                        'UF_BASE_SHOP_LOC' => $this->locationService->getLocationPathCodes($locationCode)
                    ])
            ];
        };

        try {
            $result = (new BitrixCache())
                ->withId(__METHOD__ . $locationCode)
                ->withTag('catalog:store')
                ->resultOf($getStores);

            /** @var StoreCollection $stores */
            $stores = $result['result'];
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf(
                    'failed to get base shops for location: %s',
                    $e->getMessage()
                ),
                ['location' => $locationCode]
            );
        }

        return $stores ?? new StoreCollection();
    }

    /**
     * @param string $locationCode
     * @param string $type
     *
     * @return StoreCollection
     */
    public function getSubRegionalStores(string $locationCode, string $type = self::TYPE_ALL): StoreCollection
    {
        if ($subregionCode = $this->locationService->findLocationSubRegion($locationCode)['CODE']) {
            $getStores = function () use ($type, $subregionCode) {
                return ['result' => $this->getStores($type, ['UF_SUBREGION' => $subregionCode])];
            };

            try {
                $result = (new BitrixCache())
                    ->withId(__METHOD__ . $subregionCode . $type)
                    ->withTag('catalog:store')
                    ->resultOf($getStores);

                /** @var StoreCollection $stores */
                $stores = $result['result'];
            } catch (\Exception $e) {
                $this->logger->error(
                    sprintf(
                        'failed to get stores for location: %s',
                        $e->getMessage()
                    ),
                    ['location' => $locationCode, 'type' => $type]
                );
            }
        }

        return $stores ?? new StoreCollection();
    }

    /**
     * @param string $locationCode
     * @param string $type
     *
     * @return StoreCollection
     */
    public function getRegionalStores(string $locationCode, string $type = self::TYPE_ALL): StoreCollection
    {
        if ($regionCode = $this->locationService->findLocationRegion($locationCode)['CODE']) {
            $getStores = function () use ($type, $regionCode) {
                return ['result' => $this->getStores($type, ['UF_REGION' => $regionCode])];
            };

            try {
                $result = (new BitrixCache())
                    ->withId(__METHOD__ . $regionCode . $type)
                    ->withTag('catalog:store')
                    ->resultOf($getStores);

                /** @var StoreCollection $stores */
                $stores = $result['result'];
            } catch (\Exception $e) {
                $this->logger->error(
                    sprintf(
                        'failed to get stores for location: %s',
                        $e->getMessage()
                    ),
                    ['location' => $locationCode, 'type' => $type]
                );
            }
        }

        return $stores ?? new StoreCollection();
    }

    /**
     * @return StoreCollection
     */
    public function getSupplierStores(): StoreCollection
    {
        $getStores = function () {
            $storeCollection = $this->storeRepository->findBy(
                $this->getTypeFilter(self::TYPE_SUPPLIER)
            );

            return ['result' => $storeCollection];
        };

        try {
            $result = (new BitrixCache())
                ->withId(__METHOD__)
                ->withTag('catalog:store')
                ->resultOf($getStores)['result'];
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('failed to get supplier stores: %s', $e->getMessage())
            );
            $result = new StoreCollection();
        }

        return $result;
    }

    /**
     * @param array $filter
     *
     * @param array $select
     *
     * @throws \Exception
     * @return array
     */
    public function getMetroInfo(array $filter = [], array $select = ['*']): array
    {
        $highloadStation = HLBlockFactory::createTableObject('MetroStations');
        $branchIds = [];
        $result = [];
        $query = $highloadStation::query();
        if (!empty($filter)) {
            $query->setFilter($filter);
        }
        $res = $query->setSelect($select)->exec();
        while ($item = $res->fetch()) {
            $result[$item['ID']] = $item;
            $branchIds[$item['ID']] = $item['UF_BRANCH'];
        }

        if (\is_array($branchIds) && !empty($branchIds)) {
            $highloadBranch = HLBlockFactory::createTableObject('MetroWays');
            $res = $highloadBranch::query()->setFilter(['ID' => array_unique($branchIds)])->setSelect(['*'])->exec();
            $reverseBranchIds = [];
            foreach ($branchIds as $id => $branch) {
                $reverseBranchIds[$branch][] = $id;
            }
            while ($item = $res->fetch()) {
                if (\is_array($reverseBranchIds[$item['ID']]) && !empty($reverseBranchIds[$item['ID']])) {
                    foreach ($reverseBranchIds[$item['ID']] as $id) {
                        $item['CLASS'] = $item['UF_CLASS'] ?? '';
                        $result[$id]['BRANCH'] = $item;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param Store $store
     *
     * @return bool
     */
    public function saveStore(Store $store): bool
    {
        $result = false;
        try {
            if ($store->getId()) {
                $result = $this->storeRepository->update($store);
            } else {
                $result = $this->storeRepository->create($store);
            }
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('failed to save store: %s: %s', \get_class($e), $e->getMessage()),
                [
                    'id'    => $store->getId(),
                    'xmlId' => $store->getXmlId(),
                ]
            );
        }

        return $result;
    }

    /**
     * @param array $filter
     * @param array $select
     *
     * @throws \Exception
     * @return array
     */
    public function getServicesInfo(array $filter = [], array $select = ['*']): array
    {
        $highloadServices = HLBlockFactory::createTableObject('StoreServices');

        $result = [];
        $query = $highloadServices::query();
        if (!empty($filter)) {
            $query->setFilter($filter);
        }
        $res = $query->setSelect($select)->exec();
        while ($item = $res->fetch()) {
            $result[$item['ID']] = $item;
        }

        return $result;
    }

    /**
     * @todo выпилить (см. StoreListController)
     * @param array $params
     *
     * @throws ArgumentException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws FileNotFoundException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @return array
     */
    public function getStoresInfo(array $params = []): array
    {
        if (!isset($params['storesAlways'])) {
            $params['storesAlways'] = false;
        }

        /** отсееваем магазины без названия и без местоположения */
        $params['filter']['!ADDRESS'] = ['', null];
        $params['filter']['!UF_LOCATION'] = ['', null];
        $locRegion = '';

        $loc = $params['filter']['UF_LOCATION'];
        if ($this->deliveryService->getDeliveryZoneByLocation($loc) === DeliveryService::ZONE_4) {
            $loc = '';
            unset($params['activeStoreId']);
            //сортировку - $params['order'] - сохраняем
            $serviceFilter = null;
            $params['filter'] = [];
            /** отсееваем магазины без названия и без местоположения */
            $params['filter']['!ADDRESS'] = ['', null];
            $params['filter']['!UF_LOCATION'] = ['', null];

            //сохраняем фильтрацию по сервисам
            if (!empty($params['filter']['UF_SERVICES'])) {
                $serviceFilter = $params['filter']['UF_SERVICES'];
            }
            if ($serviceFilter !== null) {
                $params['filter']['UF_SERVICES'] = $serviceFilter;
                unset($serviceFilter);
            }
            $storeCollection = $this->getStoreCollection($params);
        } else {
            /** city */
            $storeCollection = $this->getStoreCollection($params);
            /** region */
            if (!empty($loc) && $params['storesAlways'] && $storeCollection->isEmpty()) {
                $code = $params['filter']['UF_LOCATION'];
                $codeList = json_decode($code, true);
                if (\is_array($codeList)) {
                    $dadataLocationAdapter = new DaDataLocationAdapter();
                    /** @var BitrixLocation $bitrixLocation */
                    $bitrixLocation = $dadataLocationAdapter->convertFromArray($codeList);
                    $regionId = LocationService::getRegion($bitrixLocation->getRegionId());
                } else {
                    $regionId = LocationService::getRegion($code);
                }
                if ($regionId > 0) {
                    unset($params['activeStoreId']);
                    //сортировку - $params['order'] - сохраняем
                    $serviceFilter = null;
                    $params['filter'] = [];
                    /** отсееваем магазины без названия и без местоположения */
                    $params['filter']['!ADDRESS'] = ['', null];
                    $params['filter']['!UF_LOCATION'] = ['', null];

                    //сохраняем фильтрацию по сервисам
                    if (!empty($params['filter']['UF_SERVICES'])) {
                        $serviceFilter = $params['filter']['UF_SERVICES'];
                    }
                    if ($serviceFilter !== null) {
                        $params['filter']['UF_SERVICES'] = $serviceFilter;
                        unset($serviceFilter);
                    }

                    $locRegion = $regionId;
                    $params['filter'][] = [
                        'LOGIC'              => 'OR',
                        'LOCATION.PARENT_ID' => $regionId,
                        'LOCATION.REGION_ID' => $regionId,
                    ];
                    $storeCollection = $this->getStoreCollection($params);
                }
            }
        }
        if (!isset($params['returnActiveServices']) || !\is_bool($params['returnActiveServices'])) {
            $params['returnActiveServices'] = false;
        }
        if (!isset($params['returnSort']) || !\is_bool($params['returnSort'])) {
            $params['returnSort'] = false;
        }
        if (!isset($params['sortVal'])) {
            $params['sortVal'] = '';
        }
        if (!isset($params['activeStoreId'])) {
            $params['activeStoreId'] = 0;
        }

        return $this->getFormatedStoreByCollection(
            [
                'storeCollection'      => $storeCollection,
                'returnActiveServices' => $params['returnActiveServices'],
                'returnSort'           => $params['returnSort'],
                'sortVal'              => $params['sortVal'],
                'activeStoreId'        => $params['activeStoreId'],
                'region_id'            => $locRegion,
                'city_code'            => $loc,
            ]
        );
    }

    /**
     * Возвращает дату ближайшей для $date отгрузки со склада
     *
     * @param Store     $store
     * @param \DateTime $date
     * @return \DateTime
     */
    public function getStoreShipmentDate(Store $store, \DateTime $date): \DateTime
    {
        $items = [
            11 => $store->getShipmentTill11(),
            13 => $store->getShipmentTill13(),
            18 => $store->getShipmentTill18(),
        ];

        $tmpDate = clone $date;
        $currentDay = (int)$tmpDate->format('w');
        $currentHour = (int)$tmpDate->format('G');
        $results = [];

        /**
         * @var int $maxHour
         * @var array $days
         */
        foreach ($items as $maxHour => $days) {
            if (empty($days)) {
                continue;
            }

            $res = [];
            foreach ($days as $day) {
                $diff = $day - $currentDay;
                /**
                 * Если текущий день является днем отгрузки
                 */
                if ($diff === 0) {
                    /**
                     * Если текущий час меньше времени окончания отгрузки,
                     * то отгрузка в текущий день, иначе - через неделю
                     */
                    if ($currentHour < $maxHour) {
                        $res[] = 0;
                    } else {
                        $res[] = 7;
                    }
                    continue;
                }

                /**
                 * если diff < 0, то поставка на следующей неделе, соответственно, добавляем 7 дней
                 */
                $res[] = ($diff > 0) ? $diff : $diff + 7;
            }

            $results[] = min($res);
        }

        $modifier = empty($results) ? 0 : min($results);
        if ($modifier) {
            $tmpDate->modify(sprintf('+%s days', $modifier));
        }

        return $tmpDate;
    }

    /**
     *
     * @param StoreCollection $stores
     *
     * @throws \Exception
     * @return array
     */
    public function getFullStoreInfo(StoreCollection $stores): array
    {
        $servicesIds = [];
        $metroIds = [];
        /** @var Store $store */
        foreach ($stores as $store) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $servicesIds = array_merge($servicesIds, $store->getServices());
            $metro = $store->getMetro();
            if ($metro > 0) {
                $metroIds[] = $metro;
            }
        }
        $services = [];
        if (!empty($servicesIds)) {
            $services = $this->getServicesInfo(['ID' => array_unique($servicesIds)]);
        }

        $metro = [];
        if (!empty($metroIds)) {
            $metro = $this->getMetroInfo(['ID' => array_unique($metroIds)]);
        }

        return [
            $services,
            $metro,
        ];
    }

    /**
     * @param $type
     *
     * @return array
     */
    protected function getTypeFilter($type): array
    {
        $filter = [];
        switch ($type) {
            case static::TYPE_BASE_SHOP:
                $filter = ['UF_IS_SHOP' => 1, 'UF_IS_SUPPLIER' => 0, 'UF_IS_BASE_SHOP' => 1];
                break;
            case static::TYPE_SHOP:
                $filter = ['UF_IS_SHOP' => 1, 'UF_IS_SUPPLIER' => 0];
                break;
            case static::TYPE_STORE:
                $filter = ['UF_IS_SHOP' => 0, 'UF_IS_SUPPLIER' => 0];
                break;
            case static::TYPE_ALL:
                $filter = ['UF_IS_SUPPLIER' => 0];
                break;
            case static::TYPE_SUPPLIER:
                $filter = ['UF_IS_SUPPLIER' => 1];
                break;
        }

        return $filter;
    }
}
