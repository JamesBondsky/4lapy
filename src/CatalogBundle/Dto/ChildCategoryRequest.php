<?php

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\Catalog\Collection\CategoryCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\FilterSet;

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
     * @var string
     */
    protected $currentPath;
    /**
     * @var string
     */
    protected $landingDocRoot;
    /**
     * @var string
     */
    protected $landingDomain;
    /**
     * @var FilterSet
     */
    protected $filterSet;

    /**
     * @return string
     */
    public function getLandingDocRoot(): string
    {
        return $this->landingDocRoot;
    }

    /**
     * @param string $landingPath
     *
     * @return ChildCategoryRequest
     */
    public function setLandingDocRoot(string $landingPath): ChildCategoryRequest
    {
        $this->landingDocRoot = $landingPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getLandingDomain(): string
    {
        return $this->landingDomain;
    }

    /**
     * @param string $landingDomain
     *
     * @return ChildCategoryRequest
     */
    public function setLandingDomain(string $landingDomain): ChildCategoryRequest
    {
        $this->landingDomain = $landingDomain;

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

    /**
     * @return string
     */
    public function getCurrentPath(): string
    {
        return $this->currentPath;
    }

    /**
     * @param string $currentPath
     *
     * @return CatalogCategorySearchRequestInterface
     */
    public function setCurrentPath(string $currentPath): CatalogCategorySearchRequestInterface
    {
        $this->currentPath = $currentPath;

        return $this;
    }

    /**
     * @return FilterSet|null
     */
    public function getFilterSet(): ?FilterSet
    {
        return $this->filterSet;
    }

    /**
     * @param FilterSet $filterSet
     *
     * @return ChildCategoryRequest
     */
    public function setFilterSet(FilterSet $filterSet): ChildCategoryRequest
    {
        $this->filterSet = $filterSet;

        return $this;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getAbsoluteLandingPath($path = ''): string
    {
        return \sprintf(
            '%s%s',
            $this->getLandingDomain(),
            $this->getLandingPath($path)
        );
    }

    /**
     * @return string
     */
    public function getBaseCategoryPath(): string
    {
        return $this->getCategoryPathByCategory($this->getCategory());
    }

    /**
     * @param Category $category
     *
     * @return string
     */
    public function getCategoryPathByCategory(Category $category): string
    {
        $path = $category->getSectionPageUrl();

        if ($this->isLanding()) {
            $path = $this->getAbsoluteLandingPath($path);
        }

        return $path;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getLandingPath(string $path = ''): string {
        return \str_replace($this->getLandingDocRoot(), '', $path ?: $this->getCurrentPath());
    }
}
