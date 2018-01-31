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
    
    protected $offerId     = 0;
    
    /**
     * @return string
     */
    public function getProductSlug() : string
    {
        return $this->productSlug;
    }
    
    /**
     * @param string $productSlug
     *
     * @return ProductDetailRequest
     */
    public function setProductSlug(string $productSlug) : ProductDetailRequest
    {
        $this->productSlug = $productSlug;
    
        return $this;
    }
    
    /**
     * @return string
     */
    public function getSectionSlug() : string
    {
        return $this->sectionSlug;
    }
    
    /**
     * @param string $sectionSlug
     *
     * @return ProductDetailRequest
     */
    public function setSectionSlug(string $sectionSlug) : ProductDetailRequest
    {
        $this->sectionSlug = $sectionSlug;
    
        return $this;
    }
    
    /**
     * @return int
     */
    public function getOfferId() : int
    {
        return $this->offerId;
    }
    
    /**
     * @param int $offerId
     *
     * @return $this
     */
    public function setOfferId(int $offerId)
    {
        $this->offerId = $offerId;
        
        return $this;
    }
}
