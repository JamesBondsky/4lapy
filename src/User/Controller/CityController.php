<?php

namespace FourPaws\User\Controller;

use FourPaws\App\Application;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\Location\Exception\CityNotFoundException;
use FourPaws\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CityController extends Controller
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
    public function setAction(Request $request): JsonResponse
    {
        $code = $request->request->get('code') ?? '';
        $name = $request->request->get('name') ?? '';

        try {
            $this->userService->setSelectedCity($code, $name);
        } catch (CityNotFoundException $e) {
            return JsonErrorResponse::create($e->getMessage());
        }

        return JsonSuccessResponse::create('Город успешно выбран.');
    }

    /**
     * @return JsonResponse
     */
    public function getAction() : JsonResponse
    {
        try {
            $city = $this->userService->getSelectedCity();
        } catch (CityNotFoundException $e) {
            return JsonErrorResponse::create($e->getMessage());
        }

        return JsonSuccessResponse::createWithData('', $city);
    }
}
