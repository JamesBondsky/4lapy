<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Banner;
use FourPaws\MobileApiBundle\Dto\Object\Order;
use JMS\Serializer\Annotation as Serializer;

class BannerListResponse
{
    /**
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Banner>")
     * @var Banner[]
     */
    protected $bannerList = [];

    /**
     * @return Banner[]
     */
    public function getBannerList(): array
    {
        return $this->bannerList;
    }

    /**
     * @param Banner[] $bannerList
     *
     * @return BannerListResponse
     */
    public function setBannerList(array $bannerList): BannerListResponse
    {
        $this->bannerList = $bannerList;
        return $this;
    }
}
