<?php

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\Catalog\Model\Share;
use FourPaws\Catalog\Model\Category;

class CatalogShareRequest extends AbstractCatalogRequest implements CatalogCategorySearchRequestInterface
{
    /**
     * @var Share
     */
    protected $share;
    
    /**
     * @var Category
     */
    protected $category;
    
    /**
     * @return Share
     */
    public function getShare(): Share
    {
        return $this->share;
    }
    
    /**
     * @param Share $share
     *
     * @return CatalogShareRequest
     */
    public function setShare(Share $share): CatalogShareRequest
    {
        $this->share = $share;
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
     * @return CatalogCategorySearchRequestInterface
     */
    public function setCategory(Category $category): CatalogCategorySearchRequestInterface
    {
        $this->category = $category;
        return $this;
    }
}
