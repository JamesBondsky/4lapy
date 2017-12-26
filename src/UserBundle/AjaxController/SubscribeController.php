<?php

namespace FourPaws\UserBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SubscribeController
 *
 * @package FourPaws\UserBundle\AjaxController
 * @Route("/subscribe")
 */
class SubscribeController extends Controller
{
    /**
     * @Route("/subscribe/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonErrorResponse
     */
    public function subscribeAction(Request $request)
    {
        $type  = $request->get('type', '');
        $email = $request->get('email', '');
        if ($type !== 'profile') {
            //$arSubscribes
        } else {
        
        }
    
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return JsonErrorResponse::create('Неверный email');
        }
    
        if (1 === 2) {
            /** @todo Добавление в ExpertSender */
            JsonSuccessResponse::create('Вы успешно подписаны');
        }
    
        return JsonErrorResponse::create('Неизвестаня ошибка');
    }
}
