<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\InfoRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Services\Api\InfoService as ApiInfoService;

class InfoController extends FOSRestController
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
     * Получить статичные разделы
     *
     * @todo Статичные страницы, Вакансии, Конкурсы, Условия доставки
     * @Rest\Get("/info/")
     * @Rest\View()
     *
     * @param InfoRequest $infoRequest
     *
     * @return ApiResponse
     */
    public function getInfoAction(InfoRequest $infoRequest): ApiResponse
    {
        return (new ApiResponse())
            ->setData([
                'info' => $this->apiInfoService->getInfo(
                    $infoRequest->getType(),
                    $infoRequest->getInfoId(),
                    $infoRequest->getFields(),
                    $infoRequest->getOfferTypeCode()
                )
            ]);
    }
}
