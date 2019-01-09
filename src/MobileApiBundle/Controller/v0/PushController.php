<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\PostPushTokenRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Security\ApiTokenListener;
use FourPaws\MobileApiBundle\Services\Api\PushMessagesService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations\Parameter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class PushController
 * @package FourPaws\MobileApiBundle\Controller
 * @Security("has_role('REGISTERED_USERS')")
 * @todo    after create notification
 */
class PushController extends FOSRestController
{
    /**
     * @var PushMessagesService
     */
    private $pushMessagesService;

    public function __construct(
        PushMessagesService $pushMessagesService
    )
    {
        $this->pushMessagesService = $pushMessagesService;
    }
    /**
     * @Rest\Get("/personal_messages/")
     * @Rest\View()
     */
    public function getAction()
    {
        // ToDo: по токену который есть у юзера в табличке api_user_session выбираем из EventTable все сообщения и возвращаем массив
        /*
         * 	$arResult['messages'][] = array(
                'id' => $arEvent['ID'],
                'text' => $arEvent['MESSAGE']['TITLE'],
                'date' => $arEvent['DATE_TIME_EXEC']->format("d.m.Y"),
                'read' => ($arEvent['VIEWED'] == 'Y') ? true : false,
                'options' => array(
                    'type' => $arEvent['MESSAGE']['TYPE'],
                    'id' => $arEvent['MESSAGE']['ID']
                    )
                );
         */
        return (new Response())
            ->setData(['messages' => []]);
    }

    /**
     * @Rest\Post("/personal_messages/")
     * @Rest\View()
     */
    public function markViewedAction()
    {
        // ToDo: на вход приходит некий id сообщения. По id и по токену который есть у юзера в табличке api_user_session выбираем из таблички EventTable (push_event) сообщение и ставим ему 'VIEWED' => 'Y'
        return (new Response())
            ->setData(['result' => true]);
    }

    /**
     * @Rest\Delete()
     * @Rest\View()
     */
    public function deleteAction()
    {
        // ToDo: на вход приходит некий id сообщения. По id и по токену который есть у юзера в табличке api_user_session выбираем из таблички EventTable (push_event) сообщение и удаляем его
        return (new Response())
            ->setData(['result' => true]);
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
        $result = $this->pushMessagesService->actualizeUserPushParams($postPushTokenRequest);

        return (new Response())
            ->setData(['result' => $result]);
    }
}
