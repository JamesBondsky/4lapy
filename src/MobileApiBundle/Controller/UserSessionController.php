<?php

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Data\Start;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Services\UserSessionService;

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
     * @Rest\Get(path="/start", name="start")
     */
    public function startAction()
    {
        $response = new Response();
        try {
            $session = $this->sessionService->create();
            $response->setData(new Start($session->getToken()));
        } catch (\Exception $exception) {
            /**
             * Todo move exception handling
             */
            $response->addError(new Error($exception->getCode(), $exception->getMessage()));
        }

        return $this->view($response);
    }
}
