<?php

namespace FourPaws\SaleBundle\Dto\ShopList;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;

class ShopList
{
    /**
     * @var ArrayCollection
     *
     * @Serializer\SerializedName("items")
     * @Serializer\Type("ArrayCollection<FourPaws\SaleBundle\Dto\ShopList\Shop>")
     */
    protected $shops;

    /**
     * @var float
     *
     * @Serializer\SerializedName("avg_gps_s")
     * @Serializer\Type("float")
     */
    protected $avgLatitude = 0;

    /**
     * @var float
     *
     * @Serializer\SerializedName("avg_gps_n")
     * @Serializer\Type("float")
     */
    protected $avgLongitude = 0;

    /**
     * @var ArrayCollection
     *
     * @Serializer\SerializedName("offers")
     * @Serializer\Type("ArrayCollection<int, FourPaws\SaleBundle\Dto\ShopList\OfferInfo>")
     */
    protected $offers;

    /**
     * @return ArrayCollection
     */
    public function getShops(): ArrayCollection
    {
        return $this->shops;
    }

    /**
     * @param ArrayCollection $shops
     *
     * @return ShopList
     */
    public function setShops(ArrayCollection $shops): ShopList
    {
        $this->shops = $shops;

        return $this;
    }

    /**
     * @return float
     */
    public function getAvgLatitude(): float
    {
        return $this->avgLatitude;
    }

    /**
     * @param float $avgLatitude
     *
     * @return ShopList
     */
    public function setAvgLatitude(float $avgLatitude): ShopList
    {
        $this->avgLatitude = $avgLatitude;

        return $this;
    }

    /**
     * @return float
     */
    public function getAvgLongitude(): float
    {
        return $this->avgLongitude;
    }

    /**
     * @param float $avgLongitude
     *
     * @return ShopList
     */
    public function setAvgLongitude(float $avgLongitude): ShopList
    {
        $this->avgLongitude = $avgLongitude;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getOffers(): ArrayCollection
    {
        return $this->offers;
    }

    /**
     * @param ArrayCollection $offers
     *
     * @return ShopList
     */
    public function setOffers(ArrayCollection $offers): ShopList
    {
        $this->offers = $offers;

        return $this;
    }
}
