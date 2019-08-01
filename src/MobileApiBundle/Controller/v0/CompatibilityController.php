<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Request\CompatibilityRequest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Services\Api\CompatibilityService as ApiCompatibilityService;

class CompatibilityController extends BaseController
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
    public function checkCompatibilityAction(CompatibilityRequest $compatibilityRequest): ApiResponse
    {
        // Метод вызывается только в старом приложении, поэтому теперь всегда возвращаем blocked
        return (new ApiResponse())
            ->setData(
                [
                    'blocked' => true,
                    'blockedTitle' => 'Версия не поддерживается!',
                    'blockedMessage' => 'К сожалению, эта версия приложения устарела и больше не поддерживается. Пожалуйста, установите последнюю версию приложения.',
                ]
            );
    }
}
