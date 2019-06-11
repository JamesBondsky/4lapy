<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\MobileApiBundle\Dto\Request\CityNearestRequest;
use FourPaws\MobileApiBundle\Dto\Request\CitySearchRequest;
use FourPaws\MobileApiBundle\Dto\Request\MetroStationsRequest;
use FourPaws\MobileApiBundle\Dto\Request\StreetsListRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\MetroStationsResponse;
use FourPaws\MobileApiBundle\Exception\NoneMetroInCityException;
use FourPaws\MobileApiBundle\Services\Api\CityService as ApiCityService;
use FourPaws\MobileApiBundle\Services\Api\LocationService as ApiLocationService;

class LocationController extends FOSRestController
{
    /** @var ApiLocationService */
    private $apiLocationService;

    /** @var ApiCityService */
    private $apiCityService;

    public function __construct(
        ApiLocationService $apiLocationService,
        ApiCityService $apiCityService
    ) {
        $this->apiLocationService = $apiLocationService;
        $this->apiCityService = $apiCityService;
    }

    /**
     * @Rest\Get("/metro_stations/")
     * @Rest\View()
     *
     * @param MetroStationsRequest $metroStationsRequest
     *
     * @return MetroStationsResponse
     * @throws NoneMetroInCityException
     */
    public function getMetroStationsAction(MetroStationsRequest $metroStationsRequest): MetroStationsResponse
    {
        return new MetroStationsResponse($this->apiLocationService->getMetroLinesWithStations($metroStationsRequest));
    }

    /**
     * @Rest\Get("/city_search/")
     * @Rest\View()
     *
     * @param CitySearchRequest $request
     *
     * @return Response
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function citySearchAction(CitySearchRequest $request): Response
    {
        return new Response(
            $this->apiCityService->filterTypeId(
                $this->apiCityService->search($request->getQuery(), 50),
                3,
                6
            )->getValues()
        );
    }

    /**
     * @Rest\Get("/city_list/")
     * @Rest\View()
     *
     * @return Response
     * @throws IblockNotFoundException
     */
    public function defaultCityListAction(): Response
    {
        return new Response($this->apiCityService->getDefaultCity());
    }

    /**
     * @Rest\Get("/city_list_users/")
     * @Rest\View()
     *
     * @return Response
     * @throws IblockNotFoundException
     * @throws ObjectPropertyException
     */
    public function defaultCityListUsersAction(): Response
    {
        return new Response($this->apiCityService->getDefaultCities());
    }

    /**
     * @Rest\Get("/city_nearest/")
     * @Rest\View()
     *
     * @param CityNearestRequest $cityNearestRequest
     *
     * @return Response
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws NotFoundException
     */
    public function cityNearestAction(CityNearestRequest $cityNearestRequest): Response
    {
        $lat = $cityNearestRequest->getLat();
        $lon = $cityNearestRequest->getLon();
        return (new Response())->setData([
            'city' => $this->apiLocationService->getClosestCity($lat, $lon)
        ]);
    }

    /**
     * @Rest\Get("/street_list/")
     * @Rest\View()
     *
     * @param StreetsListRequest $streetsListRequest
     *
     * @return Response
     */
    public function getStreetListAction(StreetsListRequest $streetsListRequest)
    {
        return (new Response())->setData([
            'street_list' => $this->apiLocationService->getStreets($streetsListRequest->getId(), $streetsListRequest->getStreet())
        ]);
    }
}
