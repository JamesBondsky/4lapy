<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog;

use FourPaws\MobileApiBundle\Dto\Object\CatalogCategory;
use JMS\Serializer\Annotation as Serializer;

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
     * @var array|string[]
     * @Serializer\Type("array<string>")
     * @Serializer\SerializedName("details_html")
     */
    protected $detailsHtml = [];

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
    public function setPictureList($pictureList)
    {
        $this->pictureList = $pictureList;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getDetailsHtml()
    {
        return $this->detailsHtml;
    }

    /**
     * @param array|string[] $detailsHtml
     *
     * @return FullProduct
     */
    public function setDetailsHtml($detailsHtml)
    {
        $this->detailsHtml = $detailsHtml;
        return $this;
    }
}
