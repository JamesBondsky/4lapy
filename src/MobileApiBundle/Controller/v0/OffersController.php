<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\InfoRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Services\Api\InfoService;

class OffersController extends FOSRestController
{
    /**
     * @var InfoService
     */
    private $infoService;

    public function __construct(InfoService $infoService)
    {
        $this->infoService = $infoService;
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
                'offerTypes' => $this->infoService->getOfferTypes()
            ]);
    }
}
