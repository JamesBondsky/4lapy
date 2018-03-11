<?php

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\Catalog\Model\Category;

class RootCategoryRequest extends AbstractCatalogRequest implements CatalogCategorySearchRequestInterface
{
    /**
     * @var string
     */
    protected $categorySlug = '';

    /**
     * @var Category
     */
    protected $category;

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
