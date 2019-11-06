<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Request\BannersRequest;
use FourPaws\MobileApiBundle\Dto\Response\BannerListResponse;
use FourPaws\MobileApiBundle\Services\Api\BannerService as ApiBannerService;
use FourPaws\Helpers\TaggedCacheHelper;
use Bitrix\Main\Application;
use FourPaws\MobileApiBundle\Services\Api\UserService;

class BannerController extends BaseController
{
    /**
     * @var ApiBannerService
     */
    private $apiBannerService;

    /**
     * @var UserService
     */
    protected $apiUserService;

    private $cacheTime = 3600;
    private $cachePath = '/api/banners';

    public function __construct(
        ApiBannerService $apiBannerService,
        UserService $apiUserService
    )
    {
        $this->apiBannerService = $apiBannerService;
        $this->apiUserService = $apiUserService;
    }

    /**
     * @Rest\Get("/baner_list/")
     * @Rest\View()
     *
     * @param BannersRequest $bannersRequest
     * @return BannerListResponse
     * @throws \Bitrix\Main\SystemException
     */
    public function getBannersListAction(BannersRequest $bannersRequest): BannerListResponse
    {
        $cache = Application::getInstance()->getCache();
        $cacheId = md5(serialize([
            $bannersRequest->getCityId(),
            $bannersRequest->getSectionCode(),
        ]));
        if ($cache->startDataCache($this->cacheTime, $cacheId, $this->cachePath)) {
            $tagCache = $cache->isStarted() ? new TaggedCacheHelper($this->cachePath) : null;

            $apiResponse = $this->apiBannerService
                ->setCityId($bannersRequest->getCityId())
                ->getList($bannersRequest->getSectionCode());

            if ($tagCache) {
                TaggedCacheHelper::addManagedCacheTags([$this->cachePath]);
                $tagCache->end();
            }

            $cache->endDataCache($apiResponse);
        } else {
            $apiResponse = $cache->getVars();
        }

        /* для неавторизованных пользователей убираем баннер с квестом */
        $bannerList = $apiResponse->getBannerList();
        foreach ($bannerList as $key => $banner) {
            if ($banner->getType() === 'quest') {
                $deleteBanner = false;
                try {
                    if ($this->apiUserService->getCurrentApiUser() === null) {
                        $deleteBanner = true;
                    }
                } catch (\Exception $e) {
                    $deleteBanner = true;
                }

                if ($deleteBanner) {
                    unset($bannerList[$key]);
                }
            }
        }

        $apiResponse->setBannerList($bannerList);

        return $apiResponse;
    }

    /**
     * @Rest\Get("/promo_baner/")
     * @Rest\View()
     *
     * @param BannersRequest $bannersRequest
     * @return BannerListResponse
     * @throws \Bitrix\Main\SystemException
     */
    public function getPromoBannersAction(BannersRequest $bannersRequest): BannerListResponse
    {
        $cache = Application::getInstance()->getCache();
        $cacheId = md5(serialize([
            $bannersRequest->getCityId(),
        ]));
        if ($cache->startDataCache($this->cacheTime, $cacheId, $this->cachePath)) {
            $tagCache = $cache->isStarted() ? new TaggedCacheHelper($this->cachePath) : null;

            $response = $this->apiBannerService
                ->setCityId($bannersRequest->getCityId())
                ->getList('mobile_promo');

            if ($tagCache) {
                TaggedCacheHelper::addManagedCacheTags([$this->cachePath]);
                $tagCache->end();
            }

            $cache->endDataCache($response);
        } else {
            $response = $cache->getVars();
        }
        return $response;
    }
}
