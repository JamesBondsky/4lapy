<?php

namespace FourPaws\PersonalBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
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
    /** @var OrderSubscribeService */
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
        $return = null;
        try {
            /** @var \FourPawsPersonalCabinetOrdersSubscribeFormComponent $component */
            $component = $GLOBALS['APPLICATION']->IncludeComponent(
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

            $result = $component->arResult;
            $actionResult = $result['SUBSCRIBE_ACTION'] ?? [];
            if ($actionResult && $actionResult['SUCCESS'] === 'Y') {
                $return = JsonSuccessResponse::create(
                    $actionResult['TYPE'] === 'CREATE' ? 'Подписка на доставку выполнена' : 'Подписка на доставку изменена',
                    200,
                    [],
                    [
                        'reload' => false,
                        'redirect' => '/personal/subscribe/'
                    ]
                );
            } else {
                $errors = [];
                if ($result['ERROR']['FIELD']) {
                    foreach($result['ERROR']['FIELD'] as $fieldName => $error) {
                        /** @var \Bitrix\Main\Error $error */
                        $fieldCaption = $component->getFieldCaption($fieldName);
                        $fieldCaption = $fieldCaption ?? $fieldName;
                        $errors[$fieldName] = 'Поле "'.$fieldCaption.'": '.$error->getMessage();
                    }
                }
                if ($result['ERROR']['EXEC']) {
                    foreach($result['ERROR']['EXEC'] as $errName => $error) {
                        /** @var \Bitrix\Main\Error $error */
                        $errors[$errName] = $error->getMessage();
                    }
                }
                if ($errors) {
                    $return = JsonErrorResponse::createWithData(
                        'Обнаружены ошибки при обработке запроса',
                        [
                            'errors' => $errors
                        ],
                        200,
                        [
                            'reload' => false,
                            'redirect' => ''
                        ]
                    );
                }
            }
        } catch (\Exception $exception) {}

        if (!$return) {
            $return = JsonErrorResponse::createWithData(
                'Неизвестная ошибка. Пожалуйста, обратитесь к администратору сайта',
                [
                    'errors' => [
                        'systemError' => 'Неизвестная ошибка. Пожалуйста, обратитесь к администратору сайта'
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
        $return = null;

        if (!$return) {
            $return = JsonErrorResponse::createWithData(
                'Неизвестная ошибка. Пожалуйста, обратитесь к администратору сайта',
                [
                    'errors' => [
                        'systemError' => 'Неизвестная ошибка. Пожалуйста, обратитесь к администратору сайта'
                    ]
                ]
            );
        }

        return $return;
    }
}
