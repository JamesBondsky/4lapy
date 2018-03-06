<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\Catalog\Collection\FilterCollection;
use Symfony\Component\Validator\Constraints as Assert;

class ProductListRequest
{
    /**
     * @Assert\Count(min = 1)
     * @var array
     */
    protected $productIds = [];

    /** @var FilterCollection */
    protected $filters;

    /**
     * @return array
     */
    public function getProductIds(): array
    {
        return $this->productIds;
    }

    /**
     * @param array $productIds
     * @return ProductListRequest
     */
    public function setProductIds(array $productIds): ProductListRequest
    {
        $this->productIds = $productIds;
        return $this;
    }

    /**
     * @return FilterCollection
     */
    public function getFilters(): FilterCollection
    {
        return $this->filters;
    }

    /**
     * @param FilterCollection $filters
     * @return ProductListRequest
     */
    public function setFilters(FilterCollection $filters): ProductListRequest
    {
        $this->filters = $filters;
        return $this;
    }
}