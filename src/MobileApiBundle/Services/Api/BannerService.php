<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use \FourPaws\Catalog\Query\BannerQuery;
use \FourPaws\MobileApiBundle\Dto\Object\Banner;
use FourPaws\Catalog\Model\Banner as BannerModel;
use FourPaws\MobileApiBundle\Dto\Response\BannerListResponse;
use Doctrine\Common\Collections\ArrayCollection;

class BannerService
{
    /**
     * @var string
     */
    private $cityId = '';

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
    public function setCityId($cityId): BannerService {
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
            ->setPicture($bannerModel->getPictureForMobile())
            ->setLink($bannerModel->getLink(), $this->cityId);

        return $banner;
    }
}
