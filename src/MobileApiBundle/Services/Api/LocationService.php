<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use FourPaws\External\DaDataService;
use FourPaws\MobileApiBundle\Dto\Object\City;
use FourPaws\MobileApiBundle\Dto\Object\StreetsListItem;
use FourPaws\MobileApiBundle\Dto\Request\MetroStationsRequest;
use FourPaws\MobileApiBundle\Exception\NoneMetroInCityException;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Repository\StoreRepository;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\LocationBundle\LocationService as AppLocationService;
use FourPaws\MobileApiBundle\Services\Api\MetroService as ApiMetroService;


class LocationService
{
    /** @var StoreRepository */
    private $storeRepository;

    /** @var AppLocationService */
    private $appLocationService;

    /** @var ApiMetroService */
    private $apiMetroService;

    /** @var DaDataService */
    private $daDataService;

    public function __construct(
        StoreRepository $storeRepository,
        AppLocationService $appLocationService,
        ApiMetroService $apiMetroService,
        DaDataService $daDataService
    )
    {
        $this->storeRepository = $storeRepository;
        $this->appLocationService = $appLocationService;
        $this->apiMetroService = $apiMetroService;
        $this->daDataService = $daDataService;
    }

    public function getMetroLinesWithStations(MetroStationsRequest $metroStationsRequest)
    {
        $metroLines = $this->apiMetroService->getMetroLinesWithStations($metroStationsRequest->getCityId());
        if (!$metroLines->count()) {
            throw new NoneMetroInCityException(
                sprintf(
                    'No such metro found for city with id %s',
                    $metroStationsRequest->getCityId()
                )
            );
        }
        return $metroLines;
    }

    /**
     * @param float $lat
     * @param float $lon
     * @return City
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getClosestCity(float $lat, float $lon)
    {
        $location = $this->getClosestStoreLocation($lat, $lon);
        return (new City())
            ->setId($location['CODE'])
            ->setTitle($location['NAME'])
            ->setLatitude($location['LATITUDE'])
            ->setLongitude($location['LONGITUDE'])
            ->setHasMetro( $location['CODE'] === AppLocationService::LOCATION_CODE_MOSCOW);
    }

    public function getStreets($cityCode, $streetName): array
    {
        $city = $this->appLocationService->findLocationByCode($cityCode);

        $streetsList = [];
        $streets = $this->daDataService->getStreets($streetName, $city['NAME']);
        if (!empty($streets)) {
            foreach ($streets as $street) {
                if (!empty($street['data']['street_kladr_id'])) {
                    $streetsList[] = (new StreetsListItem())
                        ->setId($street['data']['street_kladr_id'])
                        ->setStreet($street['data']['street_with_type']);
                }
            }
        }
        return $streetsList;
    }

    /**
     * @param float $lat
     * @param float $lon
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws NotFoundException
     */
    protected function getClosestStoreLocation(float $lat, float $lon):array
    {
        /**
         * @var Store $store
         */
        $orderBy = [
            'DISTANCE_' . $lat . '_' . $lon => 'ASC'
        ];
        $store = $this->storeRepository->findBy([], $orderBy,1)->first();

        if (!$store) {
            throw new NotFoundException('не найден ближайший магазин по заданным координатам');
        }

        $location = $this->appLocationService->findLocationByCode($store->getLocation());

        if (!$location) {
            throw new NotFoundException('не найдена локация ближайшего магазина');
        }

        return $location;
    }
}
