<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Service;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Main\ArgumentException;
use Doctrine\Common\Collections\Collection;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Model\CropImageDecorator;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Location\LocationService;
use FourPaws\StoreBundle\Collection\StockCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Base as BaseEntity;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\BaseException;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Repository\StockRepository;
use FourPaws\StoreBundle\Repository\StoreRepository;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use WebArch\BitrixCache\BitrixCache;

class StoreService
{
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

    /**
     * @var StockRepository
     */
    protected $stockRepository;

    /** @var  BaseResult */
    protected $pickupDelivery;

    /** @var DeliveryService $deliveryService */
    protected $deliveryService;

    /** @var Offer[] $offers */
    private $offers;

    public function __construct(
        LocationService $locationService,
        StoreRepository $storeRepository,
        StockRepository $stockRepository,
        DeliveryService $deliveryService
    ) {
        $this->locationService = $locationService;
        $this->storeRepository = $storeRepository;
        $this->stockRepository = $stockRepository;
        $this->deliveryService = $deliveryService;
    }

    /**
     * Получить склад по ID
     *
     * @param int $id
     *
     * @throws NotFoundException
     * @return BaseEntity|bool|Store
     */
    public function getById(int $id)
    {
        $store = false;
        try {
            $store = $this->storeRepository->find($id);
        } catch (BaseException $e) {
        }

        if (!$store) {
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
     * @return StoreCollection
     * @throws ArgumentException
     */
    public function getSupplierStores(): StoreCollection
    {
        return $this->storeRepository->findBy($this->getTypeFilter(self::TYPE_SUPPLIER));
    }

    /**
     * Получить склады в текущем местоположении
     *
     * @param string $type
     *
     * @return StoreCollection
     * @throws \Exception
     * @throws ArgumentException
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
     * @param bool $strict
     *
     * @return StoreCollection
     * @throws ArgumentException
     * @throws \Exception
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

        $result = (new BitrixCache())->withId(__METHOD__ . $locationCode . $type)->resultOf($getStores);

        /** @var StoreCollection $stores */
        $stores = $result['result'];

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
     * @param $type
     *
     * @return array
     */
    public function getTypeFilter($type): array
    {
        switch ($type) {
            case self::TYPE_SHOP:
                return ['UF_IS_SHOP' => 1, 'UF_IS_SUPPLIER' => 0];
            case self::TYPE_STORE:
                return ['UF_IS_SHOP' => 0, 'UF_IS_SUPPLIER' => 0];
            case self::TYPE_ALL:
                return ['UF_IS_SUPPLIER' => 0];
            case self::TYPE_SUPPLIER:
                return ['UF_IS_SUPPLIER' => 1];
        }

        return [];
    }

    /**
     * Получить склады по массиву XML_ID
     *
     * @param array $codes
     *
     * @throws \Exception
     * @return StoreCollection
     */
    public function getMultipleByXmlId(array $codes): StoreCollection
    {
        return $this->storeRepository->findBy(['XML_ID' => $codes]);
    }

    /**
     * @param array $filter
     *
     * @param array $select
     *
     * @return array
     * @throws \Exception
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
     * @return LocationService
     */
    public function getLocationService(): LocationService
    {
        return $this->locationService;
    }

    /**
     * @param array $filter
     * @param array $select
     *
     * @return array
     * @throws \Exception
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
     * Получить наличие офферов на указанных складах
     *
     * @param Collection $offers
     * @param StoreCollection $stores
     *
     * @throws \Exception
     */
    public function getStocks(Collection $offers, StoreCollection $stores): void
    {
        foreach ($offers as $offer) {
            $offer->withStocks(
                $this->getStocksByOffer($offer)
                    ->filterByStores($stores)
            );
        }
    }

    /**
     * @param Offer $offer
     *
     * @return StockCollection
     */
    public function getStocksByOffer(Offer $offer): StockCollection
    {
        $getStocks = function () use ($offer) {
            return $this->stockRepository->findBy(
                [
                    'PRODUCT_ID' => $offer->getId(),
                ]
            );
        };

        $data = (new BitrixCache())
            ->withId(__METHOD__ . '__' . $offer->getId())
            ->withTag('catalog:stocks')
            ->withTag('catalog:stocks:' . $offer->getId())
            ->resultOf($getStocks);

        return $data['result'];
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
        $storeCollection = $this->getStoreCollection($params);
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
                'storeCollection' => $storeCollection,
                'returnActiveServices' => $params['returnActiveServices'],
                'returnSort' => $params['returnSort'],
                'sortVal' => $params['sortVal'],
                'activeStoreId' => $params['activeStoreId'],
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

            $stockResult = null;
            $storeAmount = 0;
            if ($this->pickupDelivery) {
                $stockResult = $this->pickupDelivery->getStockResult();
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
            foreach ($storeCollection as $store) {
                $metro = $store->getMetro();
                $address = $store->getAddress();

                if (!empty($metro) && !$haveMetro) {
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
                        $services[] = $service['UF_NAME'];
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
                    'id' => $store->getXmlId(),
                    'addr' => $address,
                    'adress' => $store->getDescription(),
                    'phone' => $store->getPhone(),
                    'schedule' => $store->getScheduleString(),
                    'photo' => $imageSrc,
                    'metro' => !empty($metro) ? 'м. ' . $metroList[$metro]['UF_NAME'] : '',
                    'metroClass' => !empty($metro) ? '--' . $metroList[$metro]['BRANCH']['UF_CLASS'] : '',
                    'services' => $services,
                    'gps_s' => $gpsN, //revert $gpsS
                    'gps_n' => $gpsS, //revert $gpsN
                ];

                if ($store->getId() === (int)$params['activeStoreId']) {
                    $item['active'] = true;
                }

                if ($stockResult) {
                    /** @var StockResult $stockResultByStore */
                    $stockResultByStore = $stockResult->filterByStore($store)->first();
                    $amount = $storeAmount + $stockResultByStore->getOffer()
                            ->getStocks()
                            ->filterByStore($store)
                            ->getTotalAmount();
                    $item['amount'] = $amount > 5 ? 'много' : 'мало';
                    $item['pickup'] = DeliveryTimeHelper::showTime(
                        $this->pickupDelivery,
                        [
                            'SHOW_TIME' => true,
                            'SHORT' => true,
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
            if ($params['returnActiveServices']) {
                $result['services'] = $servicesList;
            }
        }

        return $result;
    }

    /**
     * @param int $offerId
     *
     * @return StoreCollection
     */
    public function getActiveStoresByProduct(int $offerId): StoreCollection
    {
        $this->getOfferById($offerId);
        if (!$pickupDelivery = $this->getPickupDelivery()) {
            return new StoreCollection();
        }

        try {
            return $pickupDelivery->getStockResult()->getStores();
        } catch (DeliveryNotFoundException $e) {
            return new StoreCollection();
        }
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
            $result['UF_LOCATION'] = $code;
        }
        $search = $request->get('search');
        if (!empty($search)) {
            $result[] = [
                'LOGIC' => 'OR',
                '%ADDRESS' => $search,
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
     * @return null|BaseResult
     */
    protected function getPickupDelivery(): ?BaseResult
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
