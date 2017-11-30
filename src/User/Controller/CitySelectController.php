<?php

namespace FourPaws\User\Controller;

use FourPaws\App\Application;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CitySelectController extends Controller
{
    /**@var UserService */
    protected $userService;

    public function __construct()
    {
        $this->userService = Application::getInstance()->getContainer()->get('user.service');
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function selectAction(Request $request) : JsonResponse
    {
        $code = $request->request->get('code') ?? '';
        $name = $request->request->get('name') ?? '';

        try {
            $this->userService->selectCity($code, $name);
        } catch (\Exception $e) {
            return JsonErrorResponse::create($e->getMessage());
        }

        return JsonSuccessResponse::create('Город успешно выбран.');
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getListAction(Request $request) : JsonResponse
    {
        $cityList = $this->userService->getAvailableCities();

        return JsonSuccessResponse::createWithData('', $cityList);
    }
}
