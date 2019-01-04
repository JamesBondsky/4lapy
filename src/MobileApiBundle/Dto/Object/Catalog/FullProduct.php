<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog;

use FourPaws\MobileApiBundle\Dto\Object\CatalogCategory;
use JMS\Serializer\Annotation as Serializer;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct\PackingVariant;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct\SpecialOffer;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct\Bundle;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct\Flavour;

/**
 * ОбъектКаталога.ПолныйТовар
 *
 * Class FullProduct
 *
 * @package FourPaws\MobileApiBundle\Dto\Object\Catalog
 */
class FullProduct extends ShortProduct
{
    /**
     * Категория-родитель.
     *
     * @var CatalogCategory
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\CatalogCategory")
     * @Serializer\SerializedName("category")
     */
    protected $category;

    /**
     * @var array|string[]
     * @Serializer\Type("array<string>")
     * @Serializer\SerializedName("picture_list")
     */
    protected $pictureList = [];

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("details_html")
     */
    protected $detailsHtml = [];

    /**
     * @var PackingVariant[]
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct\PackingVariant>")
     * @Serializer\SerializedName("packingVariants")
     */
    protected $packingVariants = [];

    /**
     * @var SpecialOffer
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct\SpecialOffer")
     * @Serializer\SerializedName("specialOffer")
     */
    protected $specialOffer;

    /**
     * @var Flavour[]
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct\Flavour>")
     * @Serializer\SerializedName("flavours")
     */
    protected $flavours;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("nutritionFacts")
     */
    protected $nutritionFacts = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("nutritionRecommendations")
     */
    protected $nutritionRecommendations = '';

    /**
     * С этим товаром покупают
     *
     * @var Bundle[]
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct\Bundle>")
     * @Serializer\SerializedName("bundle")
     */
    protected $bundle;


    /**
     * @return CatalogCategory
     */
    public function getCategory(): CatalogCategory
    {
        return $this->category;
    }

    /**
     * @param CatalogCategory $category
     *
     * @return FullProduct
     */
    public function setCategory(CatalogCategory $category): FullProduct
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getPictureList()
    {
        return $this->pictureList;
    }

    /**
     * @param array|string[] $pictureList
     *
     * @return FullProduct
     */
    public function setPictureList($pictureList): FullProduct
    {
        $this->pictureList = $pictureList;
        return $this;
    }

    /**
     * @return string
     */
    public function getDetailsHtml()
    {
        return $this->detailsHtml;
    }

    /**
     * @param string $detailsHtml
     *
     * @return FullProduct
     */
    public function setDetailsHtml($detailsHtml): FullProduct
    {
        $this->detailsHtml = $detailsHtml;
        return $this;
    }

    /**
     * @return PackingVariant[]
     */
    public function getPackingVariants()
    {
        return $this->packingVariants;
    }

    /**
     * @param PackingVariant[] $packingVariants
     *
     * @return FullProduct
     */
    public function setPackingVariants($packingVariants): FullProduct
    {
        $this->packingVariants = $packingVariants;
        return $this;
    }

    /**
     * @return SpecialOffer
     */
    public function getSpecialOffer()
    {
        return $this->specialOffer;
    }

    /**
     * @param SpecialOffer $specialOffer
     *
     * @return FullProduct
     */
    public function setSpecialOffer(SpecialOffer $specialOffer): FullProduct
    {
        $this->specialOffer = $specialOffer;
        return $this;
    }

    /**
     * @return Flavour[]
     */
    public function getFlavours()
    {
        return $this->flavours;
    }

    /**
     * @param Flavour[] $flavours
     *
     * @return FullProduct
     */
    public function setFlavours(array $flavours): FullProduct
    {
        $this->flavours = $flavours;
        return $this;
    }

    /**
     * @return string
     */
    public function getNutritionFacts()
    {
        return $this->nutritionFacts;
    }

    /**
     * @param string $nutritionFacts
     * @return FullProduct
     */
    public function setNutritionFacts(string $nutritionFacts): FullProduct
    {
        $this->nutritionFacts = $nutritionFacts;
        return $this;
    }

    /**
     * @return string
     */
    public function getNutritionRecommendations()
    {
        return $this->nutritionFacts;
    }

    /**
     * @param string $nutritionRecommendations
     * @return FullProduct
     */
    public function setNutritionRecommendations(string $nutritionRecommendations): FullProduct
    {
        $this->nutritionRecommendations = $nutritionRecommendations;
        return $this;
    }

    /**
     * @return string
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @param ShortProduct[] $bundle
     * @return FullProduct
     */
    public function setBundle(array $bundle): FullProduct
    {
        $this->bundle = $bundle;
        return $this;
    }
}
