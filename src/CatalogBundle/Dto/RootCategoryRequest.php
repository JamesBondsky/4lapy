<?php

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\Catalog\Model\Category;

/**
 * Class RootCategoryRequest
 *
 * @package FourPaws\CatalogBundle\Dto
 */
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
     * @var Category
     */
    protected $landing;

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
     * @return bool
     */
    public function isLanding(): bool
    {
        return $this->landing !== null;
    }

    /**
     * @return Category
     */
    public function getLanding(): Category
    {
        return $this->landing;
    }

    /**
     * @param Category $landing
     *
     * @return CatalogCategorySearchRequestInterface
     */
    public function setLanding(Category $landing): CatalogCategorySearchRequestInterface
    {
        $this->landing = $landing;

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
