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
 * @Route("kkm/v1/")
 */
class KkmController extends Controller
{

    /**
     * @var KkmService $kkmService
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
     * @Route("suggestions/address/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     */
    public function suggestions(Request $request): JsonResponse
    {
        //get suggestions
        $text = $request->get('text');
        $level = $request->get('level');
        $cityKladrId = $request->get('city_kladr_id');
        $streetKladrId = $request->get('street_kladr_id');
        $res = $this->kkmService->getSuggestions($text, $level, $cityKladrId, $streetKladrId);
        if ($res['success'] == false) {
            return new JsonResponse(
                [
                    'code'    => $res['code'],
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
            KkmService::RESPONSE_STATUSES['success']['code']
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
        $text = $request->get('text');
        $res = $this->kkmService->geocode($text);
        if ($res['success'] == false) {
            return new JsonResponse(
                [
                    'code'    => $res['code'],
                    'message' => $res['error']
                ],
                $res['code']
            );
        }

        return new JsonResponse(
            [
                'address' => $res['address']
            ],
            KkmService::RESPONSE_STATUSES['success']['code']
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
        $content = json_decode($request->getContent(), true);

        $kladrId = $content['city_kladr_id'];
        $products = $content['products'];

        $res = $this->kkmService->getDeliveryRules($kladrId, $products);

        if ($res['success'] == false) {
            return new JsonResponse(
                [
                    'code'    => $res['code'],
                    'message' => $res['error']
                ],
                200
            );
        }

        return new JsonResponse(
            $res['delivery_rules'],
            KkmService::RESPONSE_STATUSES['success']['code']
        );
    }
}
