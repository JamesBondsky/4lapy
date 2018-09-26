<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Dto\ShopList;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;

class ShopList
{
    /**
     * @Serializer\SerializedName("items")
     * @Serializer\Type("ArrayCollection<FourPaws\StoreBundle\Dto\ShopList\Shop>")
     * @Serializer\SkipWhenEmpty()
     *
     * @var ArrayCollection
     */
    protected $items;

    /**
     * @Serializer\SerializedName("location_name")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $locationName;

    /**
     * @Serializer\SerializedName("services")
     * @Serializer\Type("ArrayCollection<FourPaws\StoreBundle\Dto\ShopList\Service>")
     * @Serializer\SkipWhenEmpty()
     *
     * @var ArrayCollection
     */
    protected $services;

    /**
     * @todo может быть, это нужно делать на фронте?
     * @Serializer\SerializedName("sortHtml")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $sortHtml;

    /**
     * @todo может быть, это нужно делать на фронте?
     * @Serializer\SerializedName("avg_gps_s")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $avgLatitude = 0;

    /**
     * @todo может быть, это нужно делать на фронте?
     * @Serializer\SerializedName("avg_gps_n")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $avgLongitude = 0;

    /**
     * @todo может быть, это нужно делать на фронте?
     * @Serializer\SerializedName("hideTab")
     * @Serializer\Type("bool")
     * @var bool
     */
    protected $hideTab = false;

    /**
     * @return ArrayCollection
     */
    public function getItems(): ArrayCollection
    {
        return $this->items;
    }

    /**
     * @param ArrayCollection $items
     *
     * @return ShopList
     */
    public function setItems(ArrayCollection $items): ShopList
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocationName(): string
    {
        return $this->locationName;
    }

    /**
     * @param string $locationName
     *
     * @return ShopList
     */
    public function setLocationName(string $locationName): ShopList
    {
        $this->locationName = $locationName;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getServices(): ArrayCollection
    {
        return $this->services;
    }

    /**
     * @param ArrayCollection $services
     *
     * @return ShopList
     */
    public function setServices(ArrayCollection $services): ShopList
    {
        $this->services = $services;

        return $this;
    }

    /**
     * @return string
     */
    public function getSortHtml(): string
    {
        return $this->sortHtml;
    }

    /**
     * @param string $sortHtml
     *
     * @return ShopList
     */
    public function setSortHtml(string $sortHtml): ShopList
    {
        $this->sortHtml = $sortHtml;

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
     * @return bool
     */
    public function isHideTab(): bool
    {
        return $this->hideTab;
    }

    /**
     * @param bool $hideTab
     *
     * @return ShopList
     */
    public function setHideTab(bool $hideTab): ShopList
    {
        $this->hideTab = $hideTab;

        return $this;
    }
}
