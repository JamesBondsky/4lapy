<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\MetroStationsRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\MetroStationsResponse;
use FourPaws\MobileApiBundle\Exception\NoneMetroInCityException;
use FourPaws\MobileApiBundle\Services\Api\LocationService;

class LocationController extends FOSRestController
{
    /**
     * @var LocationService
     */
    private $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * @Rest\Get("/metro_stations/")
     * @Rest\View()
     * @param MetroStationsRequest $metroStationsRequest
     *
     * @throws NoneMetroInCityException
     * @return Response
     */
    public function getMetroStationsAction(MetroStationsRequest $metroStationsRequest): Response
    {
        $metroLines = $this->locationService->getMetroLinesWithStations($metroStationsRequest->getCityId());
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
}
