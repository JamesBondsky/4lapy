<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Dto\Response;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Services\Api\PersonalOffersService as ApiPersonalOffersService;

class PersonalOffersController extends FOSRestController
{

    /**
     * @var ApiPersonalOffersService
     */
    private $apiPersonalOffersService;

    public function __construct(ApiPersonalOffersService $apiPersonalOffersService)
    {
        $this->apiPersonalOffersService = $apiPersonalOffersService;
    }

    /**
     * @Annotations\Get("/personal_offers/")
     * @Annotations\View()
     *
     * @return Response
     */
    public function getPersonalOffersAction(): Response
    {
        $response = new Response();
        $data = $this->apiPersonalOffersService->getPersonalOffers();

        if ($data['success']) {
            $response->setData($data['data']);
        } else {
            $response->setData([]);
            $response->addError(new Error((int)$data['error']['code'], $data['error']['message']));
        }

        return $response;
    }

    /**
     * @Annotations\Post("/personal_offers/email/send/")
     * @Annotations\View()
     *
     * @param Request $request
     * @return Response
     */
    public function sendEmailAction(Request $request): Response
    {
        $email = $request->get('email');
        $promocode = $request->get('promocode');

        $response = new Response();
        $data = $this->apiPersonalOffersService->sendEmail($email ?: '', $promocode ?: '');

        if ($data['success']) {
            $response->setData($data['data']);
        } else {
            $response->setData([]);
            $response->addError(new Error($data['error']['code'], $data['error']['message']));
        }

        return $response;
    }
}
