<?php


namespace FourPaws\UserBundle\AjaxController;


use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\SocServ\CSocServFB2;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class SocialAuthController
 * @package FourPaws\UserBundle\Controller
 * @Route("/oauth")
 */
class SocialAuthController extends Controller
{
    /**
     * @Route("/facebook", methods={"GET"})
     */
    public function facebook(): JsonErrorResponse
    {
        if(\CModule::IncludeModule("socialservices"))
        {
            $oAuthManager = new \CSocServAuthManager();
            $oAuthManager->Authorize(CSocServFB2::ID);
        }

        return JsonErrorResponse::create('');
    }
}
