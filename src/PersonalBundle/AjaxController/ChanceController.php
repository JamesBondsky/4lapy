<?php

namespace FourPaws\PersonalBundle\AjaxController;


use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Service\ChanceService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /**
     * @var UserAuthorizationInterface
     */
    protected $userService;

    public function __construct(ChanceService $chanceService, UserAuthorizationInterface $userService)
    {
        $this->chanceService = $chanceService;
        $this->userService = $userService;
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
        } catch (InvalidArgumentException $e) {
            return JsonErrorResponse::createWithData($e->getMessage());
        } catch (Exception $e) {
            return JsonErrorResponse::createWithData('При регистрации произошла ошибка');
        }
    }

    /**
     * @Route("/export/", methods={"GET"})
     *
     * @return Response
     */
    public function exportAction(): Response
    {
        if (!$this->userService->isAdmin()) {
            exit();
        }

        $response = new StreamedResponse();
        $response->setCallback(function () {
            $handle = fopen('php://output', 'wb+');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $this->chanceService->getExportHeader(), ';');
            foreach ($this->chanceService->getExportData() as $fields) {
                fputcsv($handle, $fields, ';');
            }

            fclose($handle);
        });

        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="chance_export.csv"');

        return $response;
    }
}
