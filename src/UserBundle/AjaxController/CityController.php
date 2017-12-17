<?php

namespace FourPaws\UserBundle\AjaxController;

use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\Location\Exception\CityNotFoundException;
use FourPaws\UserBundle\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class CityController
 * @package FourPaws\UserBundle\Controller
 * @Route("/city")
 */
class CityController extends Controller
{
    /**@var UserService */
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @Route("/set/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function setAction(Request $request): JsonResponse
    {
        $code = $request->request->get('code') ?? '';
        $name = $request->request->get('name') ?? '';
        $regionName = $request->request->get('region_name') ?? '';

        try {
            $this->userService->setSelectedCity($code, $name, $regionName);
        } catch (CityNotFoundException $e) {
            return JsonErrorResponse::create($e->getMessage());
        }

        return JsonSuccessResponse::create('Город успешно выбран.', 200, [], ['reload' => true]);
    }

    /**
     * @Route("/get/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAction(): JsonResponse
    {
        try {
            $city = $this->userService->getSelectedCity();
        } catch (CityNotFoundException $e) {
            return JsonErrorResponse::create($e->getMessage());
        }

        return JsonSuccessResponse::createWithData('', $city);
    }
}
