<?php

namespace FourPaws\StoreBundle\Service;

use FourPaws\Location\LocationService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Exception\StoreException;
use FourPaws\StoreBundle\Repository\StoreRepository;

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
    protected $repository;

    public function __construct(LocationService $locationService, StoreRepository $repository)
    {
        $this->locationService = $locationService;
        $this->repository = $repository;
    }

    /**
     * Получить склад по ID
     *
     * @param int $id
     *
     * @return Store
     * @throws NotFoundException
     */
    public function getById(int $id): Store
    {
        $store = false;
        try {
            $store = $this->repository->find($id);
        } catch (StoreException $e) {
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
     * @return Store
     * @throws NotFoundException
     */
    public function getByXmlId($xmlId): Store
    {
        $store = false;
        try {
            $store = $this->repository->findBy(['XML_ID' => $xmlId, [], 1])->first();
        } catch (StoreException $e) {
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
     * @return array
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
     * @return array
     */
    public function getByLocation(string $locationCode, string $type = self::TYPE_ALL): StoreCollection
    {
        $filter = array_merge(
            ['UF_LOCATION' => $locationCode],
            $this->getTypeFilter($type)
        );

        return $this->repository->findBy($filter);
    }

    /**
     * Получить склады по массиву XML_ID
     *
     * @param array $codes
     *
     * @return array
     */
    public function getMultipleByXmlId(array $codes): StoreCollection
    {
        return $this->repository->findBy(['XML_ID' => $codes]);
    }

    /**
     * @param $type
     *
     * @return array
     */
    protected function getTypeFilter($type)
    {
        switch ($type) {
            case self::TYPE_SHOP:
                return ['UF_IS_SHOP' => 1];
            case self::TYPE_STORE:
                return ['UF_IS_SHOP' => 0];
        }

        return [];
    }
}
