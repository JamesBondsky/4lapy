<?php

namespace FourPaws\CatalogBundle\Dto;

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
