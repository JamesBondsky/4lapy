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
 * @Route("api/kkm/v1/")
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
     * @Route("update_token/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    /**
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
    */

    /**
     * @Route("suggestions/address/", methods={"GET"})
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
     * @Route("geocode/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     */
    public function geocode(Request $request): JsonResponse
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

        $text = $request->get('text');
        $res = $this->kkmService->geocode($text);
        if ($res['success'] == false) {
            return new JsonResponse(
                [
                    'code'    => 200,
                    'message' => $res['error']
                ],
                200
            );
        }

        return new JsonResponse(
            [
                'address' => $res['address']
            ],
            200
        );
    }

    /**
     * @Route("delivery/rules/", methods={"POST"})
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

        $storeCode = $res['store_code'];

        $content = json_decode($request->getContent(), true);

        $kladrId = $content['kladr_id'];
        $products = $content['products'];

        $res = $this->kkmService->getDeliveryRules($kladrId, $products, $storeCode);

        if ($res['success'] == false) {
            return new JsonResponse(
                [
                    'code'    => 200,
                    'message' => $res['error']
                ],
                200
            );
        }

        return new JsonResponse(
            $res['delivery_rules'],
            200
        );
    }
}