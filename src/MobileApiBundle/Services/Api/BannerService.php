<?php

/**
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

    public const BANNER_LIST_SORT_BY1 = 'SORT';
    public const BANNER_LIST_SORT_ORDER1 = 'ASC';
    public const BANNER_LIST_SORT_BY2 = 'ACTIVE_FROM';
    public const BANNER_LIST_SORT_ORDER2 = 'DESC';

    /**
     * @param string $sectionCode
     * @return BannerListResponse
     */
    public function getList($sectionCode): BannerListResponse
    {
        $res = (new BannerQuery())
            ->withFilterParameter('ACTIVE', 'Y')
            ->withFilterParameter('PROPERTY_LOCATION', [$this->cityId, false])
            ->withType($sectionCode)
            ->withOrder([
                self::BANNER_LIST_SORT_BY1 => self::BANNER_LIST_SORT_ORDER1,
                self::BANNER_LIST_SORT_BY2 => self::BANNER_LIST_SORT_ORDER2,
            ])
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
     */
    protected function map(BannerModel $bannerModel) {
        $elementLink = $bannerModel->getElementLink();
        $sectionLink = $bannerModel->getSectionLink();
        $hasElementOrSectionLink = (bool)($elementLink || $sectionLink);
        $banner = (new Banner())
            ->setId($bannerModel->getId())
            ->setTitle($bannerModel->getName())
            ->setPicture($bannerModel->getPictureForMobile())
            ->setHasElementOrSectionLink($hasElementOrSectionLink)
            ->setLink($bannerModel->getLink(), $this->cityId);

        if ($bannerModel->getType()) {
            $banner->setType($bannerModel->getType());
        }

        if ($elementLink) {
            $banner->setLink($elementLink);
        } elseif ($sectionLink) {
            $banner->setLink($sectionLink);
        }

        return $banner;
    }
}
