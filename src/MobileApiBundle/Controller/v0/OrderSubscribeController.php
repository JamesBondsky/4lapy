<?php

/**
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FourPaws\MobileApiBundle\Services\Api\OrderSubscribeService as ApiOrderSubscribeService;

/**
 * Class PushController
 * @package FourPaws\MobileApiBundle\Controller
 * @Security("has_role('REGISTERED_USERS')")
 */
class OrderSubscribeController extends FOSRestController
{
    /**
     * @var ApiOrderSubscribeService;
     */
    private $apiOrderSubscribeService;

    public function __construct(
        ApiOrderSubscribeService $apiOrderSubscribeService
    )
    {
        $this->apiOrderSubscribeService = $apiOrderSubscribeService;
    }

    /**
     * @Rest\Get(path="/order_subscription/")
     * @Rest\View()
     */
    public function getOrderSubscriptionAction()
    {
        $this->apiOrderSubscribeService->getSubscriptionsForCurrentUser();
        die();
    }

    /**
     * @Rest\Post(path="/order_subscription/")
     * @Rest\View()
     */
    public function postOrderSubscriptionAction()
    {
        // toDo добавить подписку
    }

    /**
     * @Rest\Put(path="/order_subscription/")
     * @Rest\View()
     */
    public function putOrderSubscriptionAction()
    {
        // toDo обновить подписку
    }

    /**
     * @Rest\Delete(path="/order_subscription/")
     * @Rest\View()
     */
    public function deleteOrderSubscriptionAction()
    {
        // toDo удалить подписку
    }
}
