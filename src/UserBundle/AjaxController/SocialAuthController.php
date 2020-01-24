<?php


namespace FourPaws\UserBundle\AjaxController;


use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\SocServ\CSocServFB2;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
    public function facebook(Request $request): JsonErrorResponse
    {
        return JsonErrorResponse::createWithData('request', [$_REQUEST]);
        if(\CModule::IncludeModule("socialservices"))
        {
            $oAuthManager = new \CSocServAuthManager();
            $oAuthManager->Authorize(CSocServFB2::ID);
        }

        return JsonErrorResponse::create('');
    }
}
