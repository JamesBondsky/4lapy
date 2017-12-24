<?php

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Product;

class ProductDetailRequest
{
    /**
     * @var Product
     */
    protected $product;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     *
     * @return ProductDetailRequest
     */
    public function setProduct(Product $product): ProductDetailRequest
    {
        $this->product = $product;
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
     * @return ProductDetailRequest
     */
    public function setCategory(Category $category): ProductDetailRequest
    {
        $this->category = $category;
        return $this;
    }
}
