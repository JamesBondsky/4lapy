<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Exception;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Dto\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
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
     * @Rest\Get("/personal_offers/")
     * @Rest\View()
     *
     * @throws Exception
     */
    public function getPetCategoryAction()
    {
        $response = new Response();
        $data = $this->apiPersonalOffersService->getPersonalOffers();

        if (!isset($data['error'])) {
            $response->setData($data);
        } else {
            $response->setData([]);
            $response->addError(new Error($data['error']['code'], $data['error']['message']));
        }

        return $response;
    }
}
