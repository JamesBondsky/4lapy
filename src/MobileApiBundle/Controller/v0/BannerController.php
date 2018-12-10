<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations\Parameter;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\BannersRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Services\Api\BannerService as ApiBannerService;

class BannerController extends FOSRestController
{
    /**
     * @var ApiBannerService
     */
    private $apiBannerService;

    public function __construct(ApiBannerService $apiBannerService)
    {
        $this->apiBannerService = $apiBannerService;
    }

    /**
     * @Rest\Get(path="/baner_list/")
     * @Rest\View()
     *
     * @param BannersRequest $bannersRequest
     * @return ApiResponse
     */
    public function getBannersListAction(BannersRequest $bannersRequest): ApiResponse
    {
        /**
         * @todo кеширование
         */
        return (new ApiResponse())
            ->setData($this->apiBannerService->setCityId($bannersRequest->getCityId())
                ->getList($bannersRequest->getSectionCode())
            );
    }

    /**
     * @Rest\Get("/promo_baner/")
     * @Rest\View()
     *
     * @param BannersRequest $bannersRequest
     * @return ApiResponse
     */
    public function getPromoBannersAction(BannersRequest $bannersRequest): ApiResponse
    {
        return (new ApiResponse())
            ->setData($this->apiBannerService->setCityId($bannersRequest->getCityId())
                ->getList('mobile_promo'));
    }
}
