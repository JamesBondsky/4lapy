<?php

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\Catalog\Model\Category;

interface CatalogCategorySearchRequestInterface extends CatalogSearchRequestInterface
{
    /**
     * @return Category
     */
    public function getCategory(): Category;

    /**
     * @param Category $category
     *
     * @return static
     */
    public function setCategory(Category $category): CatalogCategorySearchRequestInterface;
}
