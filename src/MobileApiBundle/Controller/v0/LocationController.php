<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Bitrix\Sale\Location\LocationTable;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\External\DaDataService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\MobileApiBundle\Dto\Object\City;
use FourPaws\MobileApiBundle\Dto\Object\StreetsListItem;
use FourPaws\MobileApiBundle\Dto\Request\CityNearestRequest;
use FourPaws\MobileApiBundle\Dto\Request\CitySearchRequest;
use FourPaws\MobileApiBundle\Dto\Request\MetroStationsRequest;
use FourPaws\MobileApiBundle\Dto\Request\StreetsListRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\MetroStationsResponse;
use FourPaws\MobileApiBundle\Exception\NoneMetroInCityException;
use FourPaws\MobileApiBundle\Exception\SystemException;
use FourPaws\MobileApiBundle\Services\Api\CityService;
use FourPaws\MobileApiBundle\Services\Api\MetroService;
use FourPaws\MobileApiBundle\Services\Api\StoreService;

class LocationController extends FOSRestController
{
    /**
     * @Rest\Get("/metro_stations/")
     * @Rest\View()
     *
     * @param MetroService         $metroService
     * @param MetroStationsRequest $metroStationsRequest
     *
     * @throws \FourPaws\MobileApiBundle\Exception\NoneMetroInCityException
     * @return Response
     */
    public function getMetroStationsAction(
        MetroService $metroService,
        MetroStationsRequest $metroStationsRequest
    ): Response {
        $metroLines = $metroService->getMetroLinesWithStations($metroStationsRequest->getCityId());
        if ($metroLines->count()) {
            return new Response(
                new MetroStationsResponse($metroLines)
            );
        }
        throw new NoneMetroInCityException(
            sprintf(
                'No such metro found for city with id %s',
                $metroStationsRequest->getCityId()
            )
        );
    }

    /**
     * @Rest\Get("/city_search/")
     * @Rest\View()
     *
     * @param CityService       $cityService
     * @param CitySearchRequest $request
     *
     * @throws SystemException
     * @return Response
     */
    public function citySearchAction(CityService $cityService, CitySearchRequest $request): Response
    {
        return new Response(
            $cityService->filterTypeId(
                $cityService->search($request->getQuery(), 50),
                3,
                6
            )->getValues()
        );
    }

    /**
     * @Rest\Get("/city_list/")
     * @Rest\View()
     *
     * @param CityService $cityService
     * @return Response
     *
     * @throws \FourPaws\MobileApiBundle\Exception\SystemException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    public function defaultCityListAction(CityService $cityService): Response
    {
        return new Response($cityService->getDefaultCity());
    }

    /**
     * @Rest\Get("/city_nearest/")
     * @Rest\View()
     *
     * @param CityNearestRequest $cityNearestRequest
     * @param StoreService $storeService
     * @return Response
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\NotFoundException
     */
    public function cityNearestAction(
        CityNearestRequest $cityNearestRequest,
        StoreService $storeService
    ): Response
    {
        $lat = $cityNearestRequest->getLat();
        $lon = $cityNearestRequest->getLon();
        $location = $storeService->getClosestStoreLocation($lat, $lon);

        $city = (new City())
            ->setId($location['CODE'])
            ->setTitle($location['NAME'])
            ->setLatitude($location['LATITUDE'])
            ->setLongitude($location['LONGITUDE'])
            ->setHasMetro( $location['CODE'] === LocationService::LOCATION_CODE_MOSCOW);

        return (new Response())->setData(['city' => $city]);
    }

    /**
     * @Rest\Get("/city_nearest_by_location/")
     * @Rest\View()
     *
     * @param CityNearestRequest $cityNearestRequest
     * @return Response
     * @throws \Bitrix\Main\Db\SqlQueryException
     *
     * @deprecated Данный метод не используется в API
     */
    public function cityNearestByLocationAction(CityNearestRequest $cityNearestRequest): Response
    {
        // toDo рефакторинг
        $earthRadius = 3956;
        $lat = $cityNearestRequest->getLat();
        $lon = $cityNearestRequest->getLon();
        $tableName = LocationTable::getTableName();

        $pi180 = pi() / 180;

        $dbConnection = \Bitrix\Main\HttpApplication::getConnection();

        $location = $dbConnection->query("
            SELECT 
              LOC.*,
              LOC_NAME.NAME, 
              $earthRadius * 2 * ASIN(SQRT(POWER(SIN(($lat - abs(LOC.LATITUDE)) * $pi180 / 2),2) + COS($lat * pi()/180 ) * COS(abs(LOC.LATITUDE) *  $pi180 ) * POWER(SIN(($lon - LOC.LONGITUDE) *  $pi180 / 2), 2) ))
            as DISTANCE
            FROM $tableName as LOC
            INNER JOIN b_sale_loc_name AS LOC_NAME ON LOC_NAME.LOCATION_ID=LOC.ID
            WHERE
              LOC.LATITUDE IS NOT NULL AND LOC.LONGITUDE IS NOT NULL AND LOC.CITY_ID IS NOT NULL
            ORDER BY DISTANCE
            LIMIT 1;
        ")->fetch();

        $city = (new City())
            ->setId($location['CODE'])
            ->setTitle($location['NAME'])
            ->setLatitude($location['LATITUDE'])
            ->setLongitude($location['LONGITUDE'])
            ->setHasMetro( $location['CODE'] === LocationService::LOCATION_CODE_MOSCOW);

        return (new Response())->setData(['city' => $city]);
    }

    /**
     * @Rest\Get("/street_list/")
     * @Rest\View()
     *
     * @param StreetsListRequest $streetsListRequest
     * @param DaDataService $daDataService
     * @param LocationService $locationService
     * @return Response
     */
    public function getStreetListAction(StreetsListRequest $streetsListRequest, DaDataService $daDataService, LocationService $locationService)
    {
        $cityCode = $streetsListRequest->getId();
        $city = $locationService->findLocationByCode($cityCode);

        $streetsList = [];
        $streets = $daDataService->getStreets($streetsListRequest->getStreet(), $city['NAME']);
        if (!empty($streets)) {
            foreach ($streets as $street) {
                if (!empty($street['data']['street_kladr_id'])) {
                    $streetsList[] = (new StreetsListItem())
                        ->setId($street['data']['street_kladr_id'])
                        ->setStreet($street['data']['street_with_type']);
                }
            }
        }

        return (new Response())->setData([
            'street_list' => $streetsList
        ]);
    }

}
