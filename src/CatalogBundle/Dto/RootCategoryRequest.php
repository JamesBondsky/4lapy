<?php

namespace FourPaws\CatalogBundle\Dto;

class RootCategoryRequest
{
    /**
     * @var string
     */
    protected $categorySlug = '';

    /**
     * @return string
     */
    public function getCategorySlug(): string
    {
        return $this->categorySlug;
    }

    /**
     * @param string $categorySlug
     *
     * @return RootCategoryRequest
     */
    public function setCategorySlug(string $categorySlug): RootCategoryRequest
    {
        $this->categorySlug = $categorySlug;
        return $this;
    }
}
