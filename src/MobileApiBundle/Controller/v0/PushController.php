<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\PostPushTokenRequest;
use FourPaws\MobileApiBundle\Dto\Request\PushMessageRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Services\Api\PushMessagesService as ApiPushMessagesService;
use FourPaws\MobileApiBundle\Traits\MobileApiLoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations\Parameter;

/**
 * Class PushController
 * @package FourPaws\MobileApiBundle\Controller
 * @Security("has_role('REGISTERED_USERS')")
 */
class PushController extends FOSRestController implements LoggerAwareInterface
{
    use MobileApiLoggerAwareTrait;

    /**
     * @var ApiPushMessagesService
     */
    private $apiPushMessagesService;

    public function __construct(
        ApiPushMessagesService $apiPushMessagesService
    )
    {
        $this->apiPushMessagesService = $apiPushMessagesService;
        $this->setLogger(LoggerFactory::create('PushController', 'mobileApi'));
    }

    /**
     * @Rest\Get("/personal_messages/")
     * @Rest\View()
     * @throws \FourPaws\AppBundle\Exception\NotFoundException
     */
    public function getAction()
    {
        return (new Response())
            ->setData(['messages' => $this->apiPushMessagesService->getPushEvents()]);
    }

    /**
     * @Rest\Post("/personal_messages/")
     * @Rest\View()
     * @param PushMessageRequest $pushMessageRequest
     * @return Response
     * @throws \FourPaws\AppBundle\Exception\NotFoundException
     */
    public function markViewedAction(PushMessageRequest $pushMessageRequest)
    {
        $id = $pushMessageRequest->getId();
        return (new Response())
            ->setData(['result' => $this->apiPushMessagesService->markPushEventAsViewed($id)]);
    }

    /**
     * @Rest\Delete("/personal_messages/")
     * @Rest\View()
     * @param PushMessageRequest $pushMessageRequest
     * @return Response
     * @throws \FourPaws\AppBundle\Exception\NotFoundException
     */
    public function deleteAction(PushMessageRequest $pushMessageRequest)
    {
        $id = $pushMessageRequest->getId();
        return (new Response())
            ->setData(['result' => $this->apiPushMessagesService->deletePushEvent($id)]);
    }

    /**
     * @Rest\Post("/push_message/")
     * @Rest\View()
     * @Parameter(
     *     name="token",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="identifier token from /start/ request"
     * )
     * @param PostPushTokenRequest $postPushTokenRequest
     * @return Response
     */
    public function setPushTokenAction(PostPushTokenRequest $postPushTokenRequest)
    {
        $this->mobileApiLog()->info('Request: POST setPushTokenAction. token: ' . $postPushTokenRequest->getPushToken() . '. platform: ' . $postPushTokenRequest->getPlatform());
        $result = $this->apiPushMessagesService->actualizeUserPushParams($postPushTokenRequest);

        $response = (new Response())
            ->setData(['result' => $result]);
        $this->mobileApiLog()->info('Response: POST setPushTokenAction. token: ' . $postPushTokenRequest->getPushToken() . '. platform: ' . $postPushTokenRequest->getPlatform() . '. Response: ' . print_r($response->getData(), true));
        return $response;
    }
}
