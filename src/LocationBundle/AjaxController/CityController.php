<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\LocationBundle\AjaxController;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Exception;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\LocationBundle\LocationService;
use FourPaws\MobileApiBundle\Services\Api\CityService as ApiCityService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
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

    /** @var ApiCityService */
    private $apiCityService;

    public function __construct(LocationService $locationService, ApiCityService $apiCityService)
    {
        $this->locationService = $locationService;
        $this->apiCityService = $apiCityService;
    }

    /**
     * @Route(
     *     "/list/",
     *      methods={"GET"},
     *      name="location.city.list"
     * )
     *
     * @return JsonResponse
     * @throws ServiceCircularReferenceException
     * @throws IblockNotFoundException
     * @throws Exception
     * @throws ServiceNotFoundException
     */
    public function listAction(): JsonResponse
    {
        return JsonSuccessResponse::createWithData(
            '',
            $this->locationService->getAvailableCities()
        );
    }

    /**
     * @Route(
     *     "/select/list/",
     *      methods={"GET"},
     *      name="location.city.select.list"
     * )
     *
     * @return JsonResponse
     * @throws ServiceCircularReferenceException
     * @throws Exception
     * @throws ServiceNotFoundException
     */
    public function citySelectAction(): JsonResponse
    {
        global $APPLICATION;
        ob_start();
        $APPLICATION->IncludeComponent('fourpaws:city.selector', 'popup', ['GET_STORES' => true], null,
            ['HIDE_ICONS' => 'Y']);
        $html = ob_get_clean();
        return new JsonResponse([
            'html' => $html,
        ]);
    }

    /**
     * @Route("/suggest/address", methods={"POST"}, name="location.city.suggest.address")
     * @param Request $request
     * @return JsonResponse
     */
    public function getAddress(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent());

        $query = $content->query;
        $limit = intval($content->count);

        /**
         * Баг, когда местоположения имеют одинаковые названия
         * Специально фейлим запрос, чтобы инфромация о выбранном городе взялась в фронта, а не из этого запроса
         */
        if ($limit === 1) {
            return new JsonResponse([]);
        }

        $exact = $limit === 1;
        $filter = [];

        $locations = $this->locationService->findLocationNew(
            array_merge([($exact ? '=' : '?') . 'NAME.NAME_UPPER' => ToUpper($query)], $filter),
            $limit,
            true
        );

        $result = $this->apiCityService->convertInDadataFormat($locations);

        return new JsonResponse([
            'suggestions' => $result,
        ]);
    }

    /**
     * @Route("/suggest/address", methods={"OPTIONS"})
     */
    public function getAddressOption(): JsonResponse
    {
        return new JsonResponse([]);
    }

    /**
     * @Route("/status/address", methods={"GET"})
     */
    public function getAddressGet(): JsonResponse
    {
        return new JsonResponse([]);
    }
}
