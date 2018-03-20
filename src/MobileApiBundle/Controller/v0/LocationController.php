<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\CitySearchRequest;
use FourPaws\MobileApiBundle\Dto\Request\MetroStationsRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\MetroStationsResponse;
use FourPaws\MobileApiBundle\Exception\NoneMetroInCityException;
use FourPaws\MobileApiBundle\Exception\SystemException;
use FourPaws\MobileApiBundle\Services\Api\CityService;
use FourPaws\MobileApiBundle\Services\Api\MetroService;

class LocationController extends FOSRestController
{
    /**
     * @Rest\Get("/metro_stations/")
     * @Rest\View()
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
            )
        );
    }
}
