<?php

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\Catalog\Model\Brand;
use FourPaws\Catalog\Model\Category;

class CatalogBrandRequest extends AbstractCatalogRequest
{
    /**
     * @var Brand
     */
    protected $brand;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @return Brand
     */
    public function getBrand(): Brand
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     *
     * @return CatalogBrandRequest
     */
    public function setBrand(Brand $brand): CatalogBrandRequest
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     *
     * @return CatalogBrandRequest
     */
    public function setCategory(Category $category): CatalogBrandRequest
    {
        $this->category = $category;
        return $this;
    }
}
