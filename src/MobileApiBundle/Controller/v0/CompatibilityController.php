<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FourPaws\MobileApiBundle\Dto\Request\CompatibilityRequest;
use Swagger\Annotations\Parameter;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Services\Api\CompatibilityService as ApiCompatibilityService;

class CompatibilityController extends FOSRestController
{
    /**
     * @var ApiCompatibilityService
     */
    private $apiCompatibilityService;

    public function __construct(ApiCompatibilityService $apiCompatibilityService)
    {
        $this->apiCompatibilityService = $apiCompatibilityService;
    }

    /**
     * @Rest\Get(path="/mobile_version/")
     * @Rest\View()
     *
     * @param CompatibilityRequest $compatibilityRequest
     *
     * @return ApiResponse
     */
    public function getBannersListAction(CompatibilityRequest $compatibilityRequest): ApiResponse
    {
        return (new ApiResponse())
            ->setData(
                [
                    'blocked' => $this->apiCompatibilityService->isBlocked(
                        $compatibilityRequest->getOsType(),
                        $compatibilityRequest->getBuildVersion()
                    )
                ]
            );
    }
}
