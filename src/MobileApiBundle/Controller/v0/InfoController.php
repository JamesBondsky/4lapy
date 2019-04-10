<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Bitrix\Main\Application;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\MobileApiBundle\Dto\Request\InfoRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Services\Api\InfoService as ApiInfoService;

class InfoController extends FOSRestController
{
    /**
     * @var ApiInfoService
     */
    private $apiInfoService;
    private $cacheTime = 3600;
    private $cachePath = '/api/info';

    public function __construct(ApiInfoService $apiInfoService)
    {
        $this->apiInfoService = $apiInfoService;
    }

    /**
     * Получить статичные разделы
     *
     * @todo Вакансии, Конкурсы
     * @Rest\Get("/info/")
     * @Rest\View()

     * @param InfoRequest $infoRequest
     * @return ApiResponse
     * @throws \Bitrix\Main\SystemException
     *
     */
    public function getInfoAction(InfoRequest $infoRequest): ApiResponse
    {
        $cache = Application::getInstance()->getCache();
        $cacheId = md5(serialize([
            $infoRequest->getCityId(),
            $infoRequest->getFields(),
            $infoRequest->getType(),
            $infoRequest->getOfferTypeCode(),
        ]));
        if ($cache->startDataCache($this->cacheTime, $cacheId, $this->cachePath)) {
            $tagCache = $cache->isStarted() ? new TaggedCacheHelper($this->cachePath) : null;

            $apiResponse = (new ApiResponse())
                ->setData([
                    'info' => $this->apiInfoService->getInfo(
                        $infoRequest->getType(),
                        $infoRequest->getInfoId(),
                        $infoRequest->getFields(),
                        $infoRequest->getOfferTypeCode()
                    )
                ]);

            if ($tagCache) {
                TaggedCacheHelper::addManagedCacheTags([$this->cachePath]);
                $tagCache->end();
            }

            $cache->endDataCache($apiResponse);
        } else {
            $apiResponse = $cache->getVars();
        }

        return $apiResponse;
    }
}
