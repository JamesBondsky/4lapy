<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Catalog\Filter;
use JMS\Serializer\Annotation as Serializer;

class FilterListResponse
{
    /**
     * @Serializer\SerializedName("filter_list")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Catalog\Filter>")
     * @var Filter[]
     */
    protected $filterList = [];

    /**
     * @return Filter[]
     */
    public function getFilterList(): array
    {
        return $this->filterList;
    }

    /**
     * @param Filter[] $filterList
     *
     * @return FilterListResponse
     */
    public function setFilterList(array $filterList): FilterListResponse
    {
        $this->filterList = $filterList;
        return $this;
    }
}
