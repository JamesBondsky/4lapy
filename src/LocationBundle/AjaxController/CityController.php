<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\LocationBundle\AjaxController;

use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\LocationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CityController
 * @package FourPaws\LocationBundle\Controller
 * @Route("/city")
 */
class CityController extends Controller
{
    private const MINIMUM_QUERY_LENGTH = 2;

    private const DEFAULT_LIMIT = 10;

    private const MAX_LIMIT = 100;

    /**@var LocationService */
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * @Route(
     *     "/autocomplete/",
     *      methods={"GET"},
     *      name="location.city.autocomplete"
     * )
     *
     * @param Request $request
     *
     * @throws \Exception
     * @return JsonResponse
     */
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
            $result = $this->locationService->findLocationCity($query, '', $limit);
        } catch (CityNotFoundException $e) {
        }

        return JsonSuccessResponse::createWithData('', $result);
    }

    /**
     * @Route(
     *     "/list/",
     *      methods={"GET"},
     *      name="location.city.list"
     * )
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Exception
     * @return JsonResponse
     */
    public function listAction(): JsonResponse
    {
        return JsonSuccessResponse::createWithData(
            '',
            $this->locationService->getAvailableCities()
        );
    }
}
