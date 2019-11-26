<?php

namespace FourPaws\PersonalBundle\AjaxController;

use Exception;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\PersonalBundle\Exception\NotFoundException;
use FourPaws\PersonalBundle\Service\ChanceService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OrderSubscribeController
 *
 * @package FourPaws\PersonalBundle\AjaxController
 * @Route("/chance")
 */
class ChanceController extends Controller
{
    /**
     * @var ChanceService
     */
    protected $chanceService;

    public function __construct(ChanceService $chanceService)
    {
        $this->chanceService = $chanceService;
    }

    /**
     * @Route("/register/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @global $APPLICATION
     */
    public function registerAction(Request $request): JsonResponse
    {
        try {
            return new JsonSuccessResponse(['userChances' => $this->chanceService->registerUser()]);
        } catch (NotAuthorizedException $e) {
            return new JsonErrorResponse('Авторизуйтесь для участия');
        } catch (NotFoundException $e) {
            return new JsonErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return new JsonErrorResponse('При регистрации произошла ошибка');
        }
    }
}
