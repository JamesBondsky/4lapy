<?php

namespace FourPaws\UserBundle\AjaxController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class SubscribeController
 * @package FourPaws\UserBundle\AjaxController
 * @Route("/subscribe")
 */
class SubscribeController extends Controller
{
    /**
     * @Route("/subscribe/", methods={"POST"})
     */
    public function subscribeAction()
    {
    }
}
