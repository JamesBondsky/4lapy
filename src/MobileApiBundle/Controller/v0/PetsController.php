<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Services\Api\PetsService as ApiPetsService;

class PetsController extends FOSRestController
{

    /**
     * @var ApiPetsService
     */
    private $apiPetsService;

    public function __construct(ApiPetsService $apiPetsService)
    {
        $this->apiPetsService = $apiPetsService;
    }

    /**
     * @Rest\Get("/pets_category/")
     * @Rest\View()
     */
    public function getPetsCategoryAction()
    {
        return (new Response())->setData($this->apiPetsService->getPetsCategories());
    }
}
