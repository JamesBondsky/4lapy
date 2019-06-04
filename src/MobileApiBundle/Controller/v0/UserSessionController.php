<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\StartResponse;
use FourPaws\MobileApiBundle\Services\UserSessionService;
use FourPaws\MobileApiBundle\Traits\MobileApiLoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

class UserSessionController extends FOSRestController implements LoggerAwareInterface
{
    use MobileApiLoggerAwareTrait;

    /**
     * @var UserSessionService
     */
    private $sessionService;

    public function __construct(UserSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
        $this->setLogger(LoggerFactory::create('UserSessionController', 'mobileApi'));
    }

    /**
     * @Rest\View()
     * @Rest\Get(path="/start/", name="start")
     */
    public function startAction()
    {
        $this->mobileApiLog()->info('Request: GET startAction');
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

        $this->mobileApiLog()->info('Response: GET startAction: ' . print_r($response->getData(), true));
        return $response;
    }
}
