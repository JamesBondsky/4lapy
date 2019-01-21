<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Services\Api\InfoService as ApiInfoService;

class OffersController extends FOSRestController
{
    /**
     * @var ApiInfoService
     */
    private $apiInfoService;

    public function __construct(ApiInfoService $apiInfoService)
    {
        $this->apiInfoService = $apiInfoService;
    }

    /**
     * Получить типы акций
     *
     * @Rest\Get("/offer_types/")
     * @Rest\View()
     *
     * @return ApiResponse
     */
    public function getOfferTypesAction(): ApiResponse
    {
        return (new ApiResponse())
            ->setData([
                'offerTypes' => $this->apiInfoService->getOfferTypes()
            ]);
    }
}
