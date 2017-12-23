<?php

namespace FourPaws\CatalogBundle\Dto;

use Doctrine\Common\Collections\Collection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Search\Model\Navigation;

class CatalogRequest
{
    /**
     * @var Navigation
     */
    protected $navigation;

    /**
     * @var Collection
     */
    protected $sorts;

    /**
     * @var string
     */
    protected $searchString = '';

    /**
     * @var Category
     */
    protected $category;

    public function __construct(
        Category $category,
        Navigation $navigation,
        Collection $sorts,
        string $searchString = ''
    ) {
        $this->category = $category;
        $this->navigation = $navigation;
        $this->sorts = $sorts;
        $this->searchString = $searchString;
    }
}
