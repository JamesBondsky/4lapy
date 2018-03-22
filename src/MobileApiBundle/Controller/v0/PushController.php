<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class PushController
 * @package FourPaws\MobileApiBundle\Controller
 * @Security("hos_role('ROLE_USER')")
 * @todo    after create notification
 */
class PushController extends FOSRestController
{
    /**
     * @Rest\Get("/personal_messages/")
     * @Rest\View()
     */
    public function getAction()
    {
        return new Response();
    }

    /**
     * @Rest\Post("/personal_messages/")
     * @Rest\View()
     */
    public function markViewedAction()
    {
        return (new Response())
            ->setData(['result' => true]);
    }

    /**
     * @Rest\Delete()
     * @Rest\View()
     */
    public function deleteAction()
    {
        return (new Response())
            ->setData(['result' => true]);
    }
}
