<?php

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\Catalog\Model\Category;

class ChildCategoryRequest extends AbstractCatalogRequest
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
     * @return static
     */
    public function setCategory(Category $category): ChildCategoryRequest
    {
        $this->category = $category;
        return $this;
    }
}
