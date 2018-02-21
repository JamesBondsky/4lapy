<?php

namespace FourPaws\PersonalBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\PersonalBundle\Service\PetService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OrderSubscribeController
 *
 * @package FourPaws\PersonalBundle\AjaxController
 * @Route("/orderSubscribe")
 */
class OrderSubscribeController extends Controller
{
    /**
     * @var OrderSubscribeService
     */
    private $orderSubscribeService;
    
    public function __construct(OrderSubscribeService $orderSubscribeService) {
        $this->orderSubscribeService = $orderSubscribeService;
    }
    
    /**
     * @Route("/edit/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function editAction(Request $request) : JsonResponse
    {
        try {
            $result = $GLOBALS['APPLICATION']->IncludeComponent(
                'fourpaws:personal.orders.subscribe.form',
                '',
                [
                    'INCLUDE_TEMPLATE' => 'N',
                ],
                null,
                [
                    'HIDE_ICONS' => 'Y',
                ]
            );
            $return = JsonSuccessResponse::create(
                'Подписка на доставку изменена',
                200,
                [],
                [
                    'reload' => true,
                    'redirect' => ''
                ]
            );
        } catch (\Exception $exception) {
            $return = JsonErrorResponse::createWithData(
                'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
                [
                    'errors' => [
                        'systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта'
                    ]
                ]
            );
        }

        return $return;
    }

    /**
     * @Route("/delete/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteAction(Request $request) : JsonResponse
    {
        try {
            $result = $GLOBALS['APPLICATION']->IncludeComponent(
                'fourpaws:personal.orders.subscribe.form',
                '',
                [
                    'INCLUDE_TEMPLATE' => 'N'
                ],
                null,
                [
                    'HIDE_ICONS' => 'Y',
                ]
            );

            $return = JsonSuccessResponse::create(
                'Подписка на доставку удалена',
                200,
                [],
                [
                    'reload' => true,
                    'redirect' => ''
                ]
            );
        } catch (\Exception $exception) {
            $return = JsonErrorResponse::createWithData(
                'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
                [
                    'errors' => [
                        'systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта'
                    ]
                ]
            );
        }

        return $return;
    }
}
