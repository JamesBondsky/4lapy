<?php

namespace FourPaws\PersonalBundle\AjaxController;

use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
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
     * @Route("/register/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     * @global $APPLICATION
     */
    public function registerAction(Request $request): JsonResponse
    {
        try {
            return JsonSuccessResponse::createWithData('', ['userChances' => $this->chanceService->registerUser($request)]);
        } catch (NotAuthorizedException $e) {
            return JsonErrorResponse::createWithData('Авторизуйтесь для участия');
        } catch (NotFoundException|InvalidArgumentException $e) {
            return JsonErrorResponse::createWithData($e->getMessage());
        } catch (Exception $e) {
            return JsonErrorResponse::createWithData('При регистрации произошла ошибка');
        }
    }
}
