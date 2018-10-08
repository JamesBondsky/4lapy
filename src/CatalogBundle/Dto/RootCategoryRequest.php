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
     * @var int
     */
    protected $filterSetId = 0;
    /**
     * @var string
     */
    protected $filterSetTarget = '';

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

    /**
     * @return int
     */
    public function getFilterSetId(): int
    {
        return $this->filterSetId;
    }

    /**
     * @param int $filterSetId
     *
     * @return RootCategoryRequest
     */
    public function setFilterSetId(int $filterSetId): RootCategoryRequest
    {
        $this->filterSetId = $filterSetId;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilterSetTarget(): string
    {
        return $this->filterSetTarget;
    }

    /**
     * @param string $filterSetTarget
     *
     * @return RootCategoryRequest
     */
    public function setFilterSetTarget(string $filterSetTarget): RootCategoryRequest
    {
        $this->filterSetTarget = $filterSetTarget;

        return $this;
    }
}
