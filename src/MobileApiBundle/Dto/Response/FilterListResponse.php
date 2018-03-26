<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Response;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\Filter;
use JMS\Serializer\Annotation as Serializer;

class FilterListResponse
{
    /**
     * @Serializer\SerializedName("filter_list")
     * @Serializer\Type("ArrayCollection<FourPaws\MobileApiBundle\Dto\Object\Catalog\Filter>")
     * @var Collection|Filter[]
     */
    protected $filters = [];

    public function __construct(?ArrayCollection $filters = null)
    {
        $this->filters = $filters ?: new ArrayCollection();
    }


    /**
     * @return Collection|Filter[]
     */
    public function getFilters(): Collection
    {
        return $this->filters;
    }

    /**
     * @param Collection|Filter[] $filters
     *
     * @return FilterListResponse
     */
    public function setFilters(Collection $filters): FilterListResponse
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @param Filter $filter
     *
     * @return bool
     */
    public function addFilter(Filter $filter): bool
    {
        return $this->getFilters()->add($filter);
    }

    /**
     * @param Filter $filter
     *
     * @return bool
     */
    public function removeFilter(Filter $filter): bool
    {
        return $this->getFilters()->removeElement($filter);
    }
}
