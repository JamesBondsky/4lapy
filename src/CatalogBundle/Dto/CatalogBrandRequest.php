<?php

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\Catalog\Model\Brand;
use FourPaws\Catalog\Model\Category;

class CatalogBrandRequest
{
    /**
     * @var Brand
     */
    protected $brand;
    /**
     * @var Category
     */
    protected $category;

    public function __construct(Brand $brand, Category $category)
    {
        $this->brand = $brand;
        $this->category = $category;
    }

    /**
     * @return Brand
     */
    public function getBrand(): Brand
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
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
     * @return CatalogBrandRequest
     */
    public function setCategory(Category $category): CatalogBrandRequest
    {
        $this->category = $category;
        return $this;
    }
}
