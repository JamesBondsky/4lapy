<?php

namespace FourPaws\CatalogBundle\Dto;

class ProductDetailRequest
{
    /**
     * @var string
     */
    protected $productSlug = '';

    /**
     * @var string
     */
    protected $sectionSlug = '';

    /**
     * @return string
     */
    public function getProductSlug(): string
    {
        return $this->productSlug;
    }

    /**
     * @param string $productSlug
     *
     * @return ProductDetailRequest
     */
    public function setProductSlug(string $productSlug): ProductDetailRequest
    {
        $this->productSlug = $productSlug;
        return $this;
    }

    /**
     * @return string
     */
    public function getSectionSlug(): string
    {
        return $this->sectionSlug;
    }

    /**
     * @param string $sectionSlug
     *
     * @return ProductDetailRequest
     */
    public function setSectionSlug(string $sectionSlug): ProductDetailRequest
    {
        $this->sectionSlug = $sectionSlug;
        return $this;
    }
}
