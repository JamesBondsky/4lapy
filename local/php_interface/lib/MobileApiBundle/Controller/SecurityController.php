<?php

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;

class SecurityController extends FOSRestController
{
    /**
     * @Rest\Get(path="/start", name="start")
     */
    public function startAction()
    {
        return $this->view([
            'error' => [],
            'data'  => [],
        ]);
    }
}
