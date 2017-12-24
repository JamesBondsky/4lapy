<?php

namespace FourPaws\CatalogBundle\Dto;

class RootCategoryRequest
{
    protected $category;

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     *
     * @return RootCategoryRequest
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }
}
