<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use \FourPaws\Catalog\Query\BannerQuery;
use \FourPaws\MobileApiBundle\Dto\Object\Banner;
use FourPaws\Catalog\Model\Banner as BannerModel;
use FourPaws\MobileApiBundle\Dto\Response\BannerListResponse;
use FourPaws\UserBundle\Service\UserService as UserBundleService;
use Doctrine\Common\Collections\ArrayCollection;

class BannerService
{
    /**
     * @var UserBundleService
     */
    private $userBundleService;

    /**
     * @var string
     */
    private $cityId = '';

    public function __construct(UserBundleService $userBundleService)
    {
        $this->userBundleService = $userBundleService;
    }

    /**
     * @param string $sectionCode
     * @return BannerListResponse
     */
    public function getList($sectionCode): BannerListResponse
    {

        $res = (new BannerQuery())
            ->withFilterParameter('ACTIVE', 'Y')
            ->withType($sectionCode)
            ->exec();
        $banners = $this->mapBanners($res->getValues())->toArray();
        return (new BannerListResponse())->setBannerList($banners);
    }

    /**
     * @param string $cityId
     * @return BannerService
     */
    public function setCityId($cityId) {
        $this->cityId = $cityId;
        return $this;
    }

    /**
     * @param array $banners
     * @return Banner[]|ArrayCollection
     */
    protected function mapBanners(array $banners) {
        return (new ArrayCollection($banners))->map(\Closure::fromCallable([$this, 'map']));
    }

    /**
     * @param BannerModel $bannerModel
     * @return Banner
     * @throws \Bitrix\Main\SystemException
     */
    protected function map(BannerModel $bannerModel) {
        $banner = (new Banner())
            ->setId($bannerModel->getId())
            ->setTitle($bannerModel->getName())
            ->setPicture($bannerModel->getDetailPageUrl())
            ->setLink($bannerModel->getLink(), $this->cityId);

        return $banner;
    }
}
