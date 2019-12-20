<?php

namespace FourPaws\PersonalBundle\AjaxController;

use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Service\Chance2Service;
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
     * @var Chance2Service
     */
    protected $chance2Service;

    /**
     * @var UserAuthorizationInterface
     */
    protected $userService;

    public function __construct(ChanceService $chanceService, Chance2Service $chance2Service, UserAuthorizationInterface $userService)
    {
        $this->chanceService = $chanceService;
        $this->chance2Service = $chance2Service;
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
        return $this->registerActionHandler($request, $this->chanceService);
    }

    /**
     * @Route("/register-2/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     * @global $APPLICATION
     */
    public function register2Action(Request $request): JsonResponse
    {
        return $this->registerActionHandler($request, $this->chance2Service);
    }

    /**
     * @param Request $request
     * @param ChanceService $chanceService
     * @return JsonResponse
     * @throws ApplicationCreateException
     */
    protected function registerActionHandler(Request $request, ChanceService $chanceService): ?JsonResponse
    {
        try {
            return JsonSuccessResponse::createWithData('', ['userChances' => $chanceService->registerUser($request)]);
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
        return $this->exportActionHandler($this->chanceService);

    }

    /**
     * @Route("/export-2/", methods={"GET"})
     *
     * @return Response
     */
    public function export2Action(): Response
    {
        return $this->exportActionHandler($this->chance2Service);
    }

    /**
     * @param ChanceService $chanceService
     * @return StreamedResponse
     */
    protected function exportActionHandler(ChanceService $chanceService): StreamedResponse
    {
        if (!$this->userService->isAdmin()) {
            exit();
        }

        $response = new StreamedResponse();
        $response->setCallback(static function () use ($chanceService) {
            $handle = fopen('php://output', 'wb+');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $chanceService->getExportHeader(), ';');
            foreach ($chanceService->getExportData() as $fields) {
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
