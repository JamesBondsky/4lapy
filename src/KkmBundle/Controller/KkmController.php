<?php

namespace FourPaws\KkmBundle\Controller;

use FourPaws\App\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use FourPaws\KkmBundle\Service\KkmService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class KkmController
 *
 * @package FourPaws\KkmBundle\Controller
 *
 * @Route("/api/kkm/v1")
 */
class KkmController extends Controller
{

    /**
     * @var KkmService
     */
    private $kkmService;

    /**
     * KkmController constructor.
     */
    public function __construct()
    {
        $this->kkmService = Application::getInstance()->getContainer()->get('kkm.service');
    }

    /**
     * @Route("/update_token/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateToken(Request $request): JsonResponse
    {
        //validate old token
        $token = $request->headers->get('token');
        $res = $this->kkmService->validateToken($token);
        if ($res['success'] == false) {
            return new JsonResponse(
                [
                    'code'    => 200,
                    'message' => $res['error']
                ],
                200
            );
        }

        //update token
        $res = $this->kkmService->updateToken($res['id']);
        if ($res['success'] == false) {
            return new JsonResponse(
                [
                    'code'    => 200,
                    'message' => $res['error']
                ],
                200
            );
        }

        //return token
        return new JsonResponse(
            [
                'success' => true,
                'token'   => $res['token']
            ],
            200
        );
    }


    /**
     * @Route("/suggestions/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     */
    public function suggestions(Request $request): JsonResponse
    {
        //validate old token
        $token = $request->headers->get('token');
        $res = $this->kkmService->validateToken($token);
        if ($res['success'] == false) {
            return new JsonResponse(
                [
                    'code'    => 200,
                    'message' => $res['error']
                ],
                200
            );
        }

        //get suggestions

        $text = $request->get('text');
        $res = $this->kkmService->getSuggestions($text);
        if ($res['success'] == false) {
            return new JsonResponse(
                [
                    'code'    => 200,
                    'message' => $res['error']
                ],
                200
            );
        }

        //return suggestions
        return new JsonResponse(
            [
                'suggestions' => $res['suggestions']
            ],
            200
        );
    }

    /**
     * @Route("/get_delivery_rules/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     */
    public function getDeliveryRules(Request $request): JsonResponse
    {
        //validate old token
        $token = $request->headers->get('token');
        $res = $this->kkmService->validateToken($token);
        if ($res['success'] == false) {
            return new JsonResponse(
                [
                    'code'    => 200,
                    'message' => $res['error']
                ],
                200
            );
        }

        $content = json_decode($request->getContent(), true);

        $kladrId = $content['kladr_id'];
        $products = $content['products'];

        $data = [
            'rc'      => true,
            'courier' => [
                'price' => 'Стоимость доставки.', //350
                'date'  => 'Массив доступных дат для доставки.', //["15.03.2019", "16.03.2019", "17.03.2019"]
                'time'  => 'Массив доступных интервалов времени.' //[1, 2]
                /**
                 * Коды интервалов времени, как в SAP:
                 * 1 - (09:00 – 18:00);
                 * 2 - (18:00 – 24:00);
                 * 3 - (08:00 – 12:00);
                 * 4 - (12:00 – 16:00);
                 * 5 - (16:00 – 20:00);
                 * 6 - (20:00 – 24:00).
                 */
            ]
        ];

        //return data
        return new JsonResponse(
            $data,
            200
        );
    }
}