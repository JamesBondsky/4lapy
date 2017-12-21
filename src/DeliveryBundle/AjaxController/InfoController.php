<?php

namespace FourPaws\DeliveryBundle\AjaxController;

use FourPaws\App\Response\JsonSuccessResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class InfoController
 * @package FourPaws\UserBundle\Controller
 * @Route("/info")
 */
class InfoController extends Controller
{
    /**
     * @Route("/get/", methods={"GET"})
     */
    public function getAction(Request $request)
    {
        $code = $request->query->get('code');
        global $APPLICATION;
        ob_start();
        $APPLICATION->IncludeComponent(
            'fourpaws:city.delivery.info',
            'delivery.page',
            ['LOCATION_CODE' => $code],
            false,
            ['HIDE_ICONS' => 'Y']
        );
        $html = ob_get_clean();

        return JsonSuccessResponse::createWithData('', ['html' => $html]);
    }
}
