<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
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
    public function subscribeAction(Request $request) : JsonResponse
    {
        $type  = $request->get('type', '');
        $email = $request->get('email', '');
        /** @todo получение подписок */
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return JsonErrorResponse::create('Неверный email');
        }
        
        /** @todo Добавление в ExpertSender */
        if (1 === 2) {
            return JsonSuccessResponse::create('Вы успешно подписаны');
        }
        
        return JsonErrorResponse::create('Неизвестаня ошибка');
    }
}
