<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Service;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Doctrine\Common\Collections\Collection;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Location\LocationService;
use FourPaws\StoreBundle\Collection\BaseCollection;
use FourPaws\StoreBundle\Collection\StockCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Base as BaseEntity;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\BaseException;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Repository\StockRepository;
use FourPaws\StoreBundle\Repository\StoreRepository;
use WebArch\BitrixCache\BitrixCache;

class StoreService
{
    /**
     * Все склады
     */
    const TYPE_ALL = 'TYPE_ALL';

    /**
     * Склады, не являющиеся магазинами
     */
    const TYPE_STORE = 'TYPE_STORE';

    /**
     * Склады, являющиеся магазинами
     */
    const TYPE_SHOP = 'TYPE_SHOP';

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

    public function __construct(
        LocationService $locationService,
        StoreRepository $storeRespository,
        StockRepository $stockRepository
    ) {
        $this->locationService = $locationService;
        $this->storeRepository = $storeRespository;
        $this->stockRepository = $stockRepository;
    }

    /**
     * Получить склад по ID
     *
     * @param int $id
     *
     * @throws NotFoundException
     * @throws \Exception
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
     * @throws \Exception
     * @return Store
     */
    public function getByXmlId($xmlId): Store
    {
        $store = false;
        try {
            $store = $this->storeRepository->findBy(
                [
                    'XML_ID' => $xmlId,
                    [],
                    1,
                ]
            )->first();
        } catch (BaseException $e) {
        }

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
     * @throws \Exception
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
     *
     * @throws \Exception
     * @return StoreCollection
     */
    public function getByLocation(string $locationCode, string $type = self::TYPE_ALL): StoreCollection
    {
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

        return $result['result'];
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
                return ['UF_IS_SHOP' => 1];
            case self::TYPE_STORE:
                return ['UF_IS_SHOP' => 0];
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
            if (!\in_array($item['UF_BRANCH'], $branchIds, true)) {
                $branchIds[$item['ID']] = $item['UF_BRANCH'];
            }
        }

        if (\is_array($branchIds) && !empty($branchIds)) {
            $highloadBranch = HLBlockFactory::createTableObject('MetroWays');
            $res = $highloadBranch::query()->setFilter(['ID' => $branchIds])->setSelect(['*'])->exec();
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
     * Получить наличие офферов на указанных складах
     *
     * @param Collection $offers
     * @param StoreCollection $stores
     */
    public function getStocks(Collection $offers, StoreCollection $stores)
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
     * @throws \Exception
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
     * @param int $offerId
     * @param string $location
     *
     * @return StoreCollection
     * @throws \Exception
     */
    public function getAvailableProductStores(Offer $offer, string $location = ''): StoreCollection
    {
        if (empty($location)) {
            $location = $this->locationService->getCurrentLocation();
        }

        $getAvailableProductStoresCurrentLocation = function () use ($offer, $location) {
            /** @var DeliveryService $deliveryService */
            $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
            $deliveries = $deliveryService->getByProduct($offer, $location);
            $pickupDelivery = null;
            foreach ($deliveries as $delivery) {
                if ($deliveryService->isInnerPickup($delivery)) {
                    $pickupDelivery = $delivery;
                }
            }
            
            if (!$pickupDelivery) {
                return new StoreCollection();
            }
            
            $stockResult = $deliveryService->getStockResultByDelivery($pickupDelivery);
            return $stockResult->getStores();
        };

        $data = (new BitrixCache())
            ->withId(__METHOD__ . '__' . $offer->getId() . '_' . $location)
            ->withTag('catalog:stocks:' . $offer->getId())
            ->withTag('catalog:shops.available')
            ->withTag('catalog:shops.available:' . $offer->getId() . '_' . $location)
            ->resultOf($getAvailableProductStoresCurrentLocation);

        return $data['result'];

    }
}
