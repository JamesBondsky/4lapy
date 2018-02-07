<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\StartResponse;
use FourPaws\MobileApiBundle\Services\UserSessionService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class UserSessionController extends FOSRestController
{
    /**
     * @var UserSessionService
     */
    private $sessionService;

    public function __construct(UserSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * @SWG\Get(
     *     @SWG\Response(
     *         response="200",
     *         description="create new session and save it",
     *         @SWG\Schema(
     *              type="object",
     *              @Model(type="FourPaws\MobileApiBundle\Dto\Data\Start"),
     *         )
     *     )
     * )
     * @Rest\Get(path="/start/", name="start")
     */
    public function startAction()
    {
        /**
         * Надо предусмотреть максимальное количество попыток
         */
        $response = new Response();
        try {
            $session = $this->sessionService->create();
            $response->setData(new StartResponse($session->getToken()));
        } catch (\Exception $exception) {
            /**
             * Todo move exception handling
             */
            $response->addError(new Error($exception->getCode(), $exception->getMessage()));
        }

        return $this->view($response);
    }
}
