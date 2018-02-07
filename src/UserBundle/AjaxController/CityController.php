<?php

namespace FourPaws\UserBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\UserBundle\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
     * @Route("/set/", methods={"POST", "GET"})
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
            $city = $this->userService->setSelectedCity($code, $name, $regionName);
        } catch (\Exception $e) {
            return JsonErrorResponse::create($e->getMessage());
        }

        return JsonSuccessResponse::createWithData('Город успешно выбран.', $city, 200, ['reload' => true]);
    }

    /**
     * @Route("/get/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAction(Request $request) : JsonResponse
    {
        try {
            $city = $this->userService->getSelectedCity();
        } catch (\Exception $e) {
            return JsonErrorResponse::create($e->getMessage());
        }

        return JsonSuccessResponse::createWithData('', $city);
    }
}
