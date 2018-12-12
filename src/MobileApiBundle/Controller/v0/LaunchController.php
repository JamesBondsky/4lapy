<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Response;

class LaunchController extends FOSRestController
{
    /**
     * @Rest\Get("/app_launch/")
     * @Rest\View()
     */
    public function launchAction()
    {
        return (new Response())
            ->setData([]);
    }
}
