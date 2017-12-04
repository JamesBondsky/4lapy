<?php

namespace FourPaws\Location\Controller;

use FourPaws\App\Application;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\Location\Exception\CityNotFoundException;
use FourPaws\Location\LocationService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CityController extends Controller
{
    const MINIMUM_QUERY_LENGTH = 2;

    const DEFAULT_LIMIT = 10;

    const MAX_LIMIT = 100;

    /**@var LocationService */
    protected $locationService;

    public function __construct()
    {
        $this->locationService = Application::getInstance()->getContainer()->get('location.service');
    }

    public function autoCompleteAction(Request $request): JsonResponse
    {
        $query = $request->query->get('query');
        $limit = $request->query->get('limit');

        $result = [];
        if (mb_strlen($query) < static::MINIMUM_QUERY_LENGTH) {
            return JsonSuccessResponse::createWithData('', $result);
        }

        if (!$limit || $limit > static::MAX_LIMIT) {
            $limit = static::DEFAULT_LIMIT;
        }

        try {
            $result = $this->locationService->findCity($query, $limit, false);
        } catch (CityNotFoundException $e) {
        }

        return JsonSuccessResponse::createWithData('', $result);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(): JsonResponse
    {
        $cityList = $this->locationService->getAvailableCities();

        return JsonSuccessResponse::createWithData('', $cityList);
    }
}
