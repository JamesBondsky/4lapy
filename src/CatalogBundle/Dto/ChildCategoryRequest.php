<?php

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\Catalog\Collection\CategoryCollection;
use FourPaws\Catalog\Model\Category;

/**
 * Class ChildCategoryRequest
 *
 * @package FourPaws\CatalogBundle\Dto
 */
class ChildCategoryRequest extends AbstractCatalogRequest implements CatalogCategorySearchRequestInterface
{
    /**
     * @var Category
     */
    protected $category;
    /**
     * @var CategoryCollection
     */
    protected $landingCollection;
    /**
     * @var bool
     */
    protected $isLanding = false;

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
     * @return CategoryCollection
     */
    public function getLandingCollection(): CategoryCollection
    {
        return $this->landingCollection;
    }

    /**
     * @param CategoryCollection $landingCollection
     *
     * @return CatalogCategorySearchRequestInterface
     */
    public function setLandingCollection(CategoryCollection $landingCollection): CatalogCategorySearchRequestInterface
    {
        $this->landingCollection = $landingCollection;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLanding(): bool
    {
        return $this->isLanding;
    }

    /**
     * @param bool $isLanding
     *
     * @return CatalogCategorySearchRequestInterface
     */
    public function setIsLanding(bool $isLanding): CatalogCategorySearchRequestInterface
    {
        $this->isLanding = $isLanding;

        return $this;
    }
}
