<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Service;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\UserMessageException;
use FourPaws\Adapter\DaDataLocationAdapter;
use FourPaws\Adapter\Model\Output\BitrixLocation;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Model\CropImageDecorator;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\WordHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Repository\StoreRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
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
     * @var LocationService
     */
    protected $locationService;

    /**
     * @var StoreRepository
     */
    protected $storeRepository;

    /** @var  PickupResultInterface */
    protected $pickupDelivery;

    /** @var DeliveryService $deliveryService */
    protected $deliveryService;

    /** @var Offer[] $offers */
    private $offers;

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
     * @param $cityCode
     *
     * @return int
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getRegion($cityCode): int
    {
        $locList = LocationTable::query()->setFilter(['=CODE' => $cityCode])->setSelect([
            'ID',
            'REGION_ID',
            'PARENT_ID',
            'TYPE_CODE'                => 'TYPE.CODE',
            'PARENTS_PARENT_ID'        => 'PARENTS.ID',
            'PARENTS_PARENT_TYPE_CODE' => 'PARENTS.TYPE.CODE',
        ])->setCacheTtl(360000)->exec()->fetchAll();
        foreach ($locList as $locItem) {
            if ($locItem['TYPE_CODE'] === 'REGION') {
                return $locItem['ID'];
            }
            if ($locItem['PARENTS_PARENT_TYPE_CODE'] === 'REGION') {
                return $locItem['PARENTS_PARENT_ID'];
            }
        }
        return 0;
    }

    /**
     * Получить склад по ID
     *
     * @param int $id
     *
     * @throws NotFoundException
     * @return Store
     */
    public function getById(int $id): Store
    {
        $store = null;

        $getStore = function () use ($id) {
            return ['result' => $this->storeRepository->find($id)];
        };

        try {
            $store = (new BitrixCache())->withId(__METHOD__ . $id)->resultOf($getStore)['result'];
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
     * @throws ArgumentException
     * @return Store
     */
    public function getByXmlId($xmlId): Store
    {
        $store = $this->storeRepository->findBy(
            [
                'XML_ID' => $xmlId,
                [],
                1,
            ]
        )->first();

        if (!$store) {
            throw new NotFoundException('Склад с XML_ID=' . $xmlId . ' не найден');
        }

        return $store;
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
    public function getByCurrentLocation($type = self::TYPE_ALL): StoreCollection
    {
        $location = $this->locationService->getCurrentLocation();

        return $this->getByLocation($location, $type);
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
    public function getByLocation(
        string $locationCode,
        string $type = self::TYPE_ALL,
        bool $strict = false
    ): StoreCollection {
        $typeFilter = $this->getTypeFilter($type);
        $getStores = function () use ($locationCode, $typeFilter) {
            $filter = array_merge(
                ['UF_LOCATION' => $locationCode],
                $typeFilter
            );

            $storeCollection = $this->storeRepository->findBy($filter);

            return ['result' => $storeCollection];
        };

        try {
            $result = (new BitrixCache())->withId(__METHOD__ . $locationCode . $type)->resultOf($getStores);

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
         * Если не нашлось ничего с типом "склад" для данного местоположения, то добавляем склады для Москвы
         */
        if (!$strict &&
            $locationCode !== LocationService::LOCATION_CODE_MOSCOW &&
            \in_array($type, [self::TYPE_STORE, self::TYPE_ALL], true) &&
            $stores->getStores()->isEmpty()
        ) {
            $moscowStores = $this->getByLocation(LocationService::LOCATION_CODE_MOSCOW, self::TYPE_STORE);
            $stores = new StoreCollection(array_merge($stores->toArray(), $moscowStores->toArray()));
        }

        return $stores;
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
            $result = (new BitrixCache())->withId(__METHOD__)->resultOf($getStores)['result'];
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('failed to get supplier stores: %s', $e->getMessage())
            );
            $result = new StoreCollection();
        }

        return $result;
    }

    /**
     * @param $type
     *
     * @return array
     */
    public function getTypeFilter($type): array
    {
        $filter = [];
        switch ($type) {
            case self::TYPE_SHOP:
                $filter = ['UF_IS_SHOP' => 1, 'UF_IS_SUPPLIER' => 0];
                break;
            case self::TYPE_STORE:
                $filter = ['UF_IS_SHOP' => 0, 'UF_IS_SUPPLIER' => 0];
                break;
            case self::TYPE_ALL:
                $filter = ['UF_IS_SUPPLIER' => 0];
                break;
            case self::TYPE_SUPPLIER:
                $filter = ['UF_IS_SUPPLIER' => 1];
                break;
        }

        return $filter;
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
     * @return StoreRepository
     */
    public function getRepository(): StoreRepository
    {
        return $this->storeRepository;
    }

    /**
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
    public function getStores(array $params = []): array
    {
        if (!isset($params['storesAlways'])) {
            $params['storesAlways'] = false;
        }
        $loc = $params['filter']['UF_LOCATION'];
        if ($params['storesAlways'] && isset($params['filter']['UF_LOCATION'])) {
            /** city */
            $storeCollection = $this->getStoreCollection($params);
            /** region */
            if (!empty($params['filter']['UF_LOCATION']) && $storeCollection->isEmpty()) {
                unset($params['activeStoreId'], $params['order']);
                $code = $params['filter']['UF_LOCATION'];
                $codeList = json_decode($code, true);
                if (\is_array($codeList)) {
                    $dadataLocationAdapter = new DaDataLocationAdapter();
                    /** @var BitrixLocation $bitrixLocation */
                    $bitrixLocation = $dadataLocationAdapter->convertFromArray($codeList);
                    $regionId = static::getRegion($bitrixLocation->getRegionId());
                } else {
                    $regionId = static::getRegion($code);
                }
                if ($regionId > 0) {
                    $locRegion = $regionId;
                    /** сбрасываем поиск */
                    unset($params['filter'][0], $params['filter']['UF_LOCATION']);
                    $params['filter'][] = [
                        'LOGIC'              => 'OR',
                        'LOCATION.PARENT_ID' => $regionId,
                        'LOCATION.REGION_ID' => $regionId,
                    ];
                    $storeCollection = $this->getStoreCollection($params);
                }
                /** moscow */
                if ($storeCollection->isEmpty()) {
                    /** сбрасываем регион */
                    unset($params['filter'][0], $locRegion);
                    $loc = $params['filter']['UF_LOCATION'] = LocationService::LOCATION_CODE_MOSCOW;
                    $storeCollection = $this->getStoreCollection($params);
                }
            }
        } else {
            $storeCollection = $this->getStoreCollection($params);
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
                'region_id'            => (string)$locRegion,
                'city_code'            => $loc,
            ]
        );
    }

    /**
     * @param array $params
     *
     * @throws ArgumentException
     * @return StoreCollection
     */
    public function getStoreCollection(array $params = []): StoreCollection
    {
        $storeRepository = $this->getRepository();
        $params['filter'] =
            array_merge((array)$params['filter'], $this->getTypeFilter($this::TYPE_SHOP));

        /** @var StoreCollection $storeCollection */
        return $storeRepository->findBy($params['filter'], (array)$params['order']);
    }

    /**
     * @param array $params
     *
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \Exception
     * @throws FileNotFoundException
     * @return array
     */
    public function getFormatedStoreByCollection(
        array $params
    ): array {
        $result = [];
        /** @var StoreCollection $storeCollection */
        $storeCollection = $params['storeCollection'];
        if (!$storeCollection->isEmpty()) {
            [$servicesList, $metroList] = $this->getFullStoreInfo($storeCollection);

            $storeAmount = 0;
            if ($this->pickupDelivery) {
                $storeAmount = reset($this->offers)->getStocks()
                    ->filterByStores(
                        $this->getByCurrentLocation(
                            static::TYPE_STORE
                        )
                    )->getTotalAmount();
            }

            /** @var Store $store */
            $avgGpsN = 0;
            $avgGpsS = 0;

            $sortHtml = '';
            if ($params['returnSort']) {
                $sortHtml = '<option value="" disabled="disabled">выберите</option>';
                $sortHtml .= '<option value="address" ' . ($params['sortVal']
                    === 'address' ? ' selected="selected" ' : '')
                    . '>по адресу</option>';
            }
            $haveMetro = false;
            foreach ($storeCollection as $key => $store) {
                $metro = $store->getMetro();

                if ($metro > 0 && !$haveMetro) {
                    $haveMetro = true;
                }

                $image = $store->getImageId();
                $imageSrc = '';
                if (!empty($image) && is_numeric($image) && $image > 0) {
                    $imageSrc =
                        CropImageDecorator::createFromPrimary($image)->setCropWidth(630)->setCropHeight(360)->getSrc();
                }

                $services = [];
                if (\is_array($servicesList) && !empty($servicesList)) {
                    foreach ($servicesList as $service) {
                        if (\in_array((int)$service['ID'], $store->getServices(), true)) {
                            $services[] = $service['UF_NAME'];
                        }
                    }
                }

                $gpsS = $store->getLongitude();
                $gpsN = $store->getLatitude();
                if ($gpsN > 0) {
                    $avgGpsN += $gpsN;
                }
                if ($gpsS > 0) {
                    $avgGpsS += $gpsS;
                }

                $item = [
                    'id'         => $store->getXmlId(),
                    'addr'       => $store->getAddress(),
                    'adress'     => WordHelper::clear($store->getDescription()),
                    'phone'      => $store->getPhone(),
                    'schedule'   => $store->getScheduleString(),
                    'photo'      => $imageSrc,
                    'metro'      => !empty($metro) ? 'м. ' . $metroList[$metro]['UF_NAME'] : '',
                    'metroClass' => !empty($metro) ? '--' . $metroList[$metro]['BRANCH']['UF_CLASS'] : '',
                    'services'   => $services,
                    'gps_s'      => $gpsN, //revert $gpsS
                    'gps_n'      => $gpsS, //revert $gpsN
                ];

                if (($params['activeStoreId'] === 'first' && $key === 0) || ($params['activeStoreId'] !== 'first' && $store->getId() === (int)$params['activeStoreId'])) {
                    $item['active'] = true;
                }

                if ($this->pickupDelivery) {
                    $tmpPickup = clone $this->pickupDelivery;
                    $tmpPickup->setSelectedStore($store);
                    if (!$tmpPickup->isSuccess()) {
                        continue;
                    }
                    /** @var StockResult $stockResultByStore */
                    $stockResultByStore = $tmpPickup->getStockResult()->first();
                    $amount = $storeAmount + $stockResultByStore->getOffer()
                            ->getStocks()
                            ->filterByStore($store)
                            ->getTotalAmount();
                    $item['amount'] = $amount > 5 ? 'много' : 'мало';
                    $item['pickup'] = DeliveryTimeHelper::showTime(
                        $this->pickupDelivery,
                        [
                            'SHOW_TIME' => true,
                            'SHORT'     => true,
                        ]
                    );
                }
                $result['items'][] = $item;
            }
            if ($haveMetro && $params['returnSort']) {
                $sortHtml .= '<option value="metro" ' . ($params['sortVal']
                    === 'metro' ? ' selected="selected" ' : '')
                    . '>по метро</option>';
            }
            $countStores = $storeCollection->count();
            $result['avg_gps_s'] = $avgGpsN / $countStores; //revert $avgGpsS
            $result['avg_gps_n'] = $avgGpsS / $countStores; //revert $avgGpsN
            $result['sortHtml'] = $sortHtml;
            /** имя местоположения для страницы магазинов */
            if (!empty($params['region_id']) || !empty($params['city_code'])) {
                if (!empty($params['region_id'])) {
                    $loc = LocationTable::query()->setFilter(['ID' => $params['region_id']])->setCacheTtl(360000)->setSelect(['LOC_NAME' => 'NAME.NAME'])->exec()->fetch();
                } else {
                    $loc = LocationTable::query()->setFilter(['=CODE' => $params['city_code']])->setCacheTtl(360000)->setSelect(['LOC_NAME' => 'NAME.NAME'])->exec()->fetch();
                }
                $result['location_name'] = $loc['LOC_NAME'];
            }
            if ($params['returnActiveServices']) {
                $result['services'] = $servicesList;
            }
        }

        return $result;
    }

    /**
     * @param int $offerId
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws DeliveryNotFoundException
     * @throws LoaderException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws UserMessageException
     * @return StoreCollection
     */
    public function getActiveStoresByProduct(int $offerId): StoreCollection
    {
        $this->getOfferById($offerId);
        if ($pickupDelivery = $this->getPickupDelivery()) {
            $result = $pickupDelivery->getBestShops();
        } else {
            $result = new StoreCollection();
        }

        return $result;
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
     * @param Request $request
     *
     * @return array
     */
    public function getFilterByRequest(Request $request): array
    {
        $result = [];
        $storesSort = $request->get('stores-sort');
        if (\is_array($storesSort) && !empty($storesSort)) {
            $result['UF_SERVICES'] = $storesSort;
        }
        $code = $request->get('code');
        if (!empty($code)) {
            $codeList = json_decode($code, true);
            if (\is_array($codeList)) {
                $dadataLocationAdapter = new DaDataLocationAdapter();
                /** @var BitrixLocation $bitrixLocation */
                $bitrixLocation = $dadataLocationAdapter->convertFromArray($codeList);
                $result['UF_LOCATION'] = $bitrixLocation->getCode();
            } else {
                $result['UF_LOCATION'] = $code;
            }
        }

        $search = $request->get('search');
        if (!empty($search)) {
            $result[] = [
                'LOGIC'          => 'OR',
                '%ADDRESS'       => $search,
                '%METRO.UF_NAME' => $search,
            ];
        }

        return $result;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getOrderByRequest(Request $request): array
    {
        $result = [];
        $sort = $request->get('sort');
        if (!empty($sort)) {
            switch ($sort) {
                case 'city':
                    $result = ['LOCATION.NAME.NAME' => 'asc'];
                    break;
                case 'address':
                    $result = ['ADDRESS' => 'asc'];
                    break;
                case 'metro':
                    $result = ['METRO.UF_NAME' => 'asc'];
                    break;
            }
        }

        return $result;
    }

    /**
     * @todo Баг при getPickupDelivery
     *
     * @param int $offerId
     *
     * @return Offer
     */
    protected function getOfferById(int $offerId): Offer
    {
        if (!isset($this->offers[$offerId])) {
            $offerQuery = new OfferQuery();
            $offerQuery->withFilter(['ID' => $offerId]);
            $this->offers[$offerId] = $offerQuery->exec()->first();
        }

        return $this->offers[$offerId];
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws LoaderException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws UserMessageException
     * @throws DeliveryNotFoundException
     * @return PickupResultInterface|null
     */
    protected function getPickupDelivery(): ?PickupResultInterface
    {
        if (!$this->pickupDelivery) {
            $deliveries = $this->deliveryService->getByProduct(reset($this->offers));

            foreach ($deliveries as $delivery) {
                if ($this->deliveryService->isInnerPickup($delivery)) {
                    $this->pickupDelivery = $delivery;
                    break;
                }
            }
        }

        return $this->pickupDelivery;
    }
}
