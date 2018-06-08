<?php

namespace FourPaws\CatalogBundle\Dto;

use FourPaws\Catalog\Model\Category;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SearchRequest
 *
 * @package FourPaws\CatalogBundle\Dto
 */
class SearchRequest extends AbstractCatalogRequest implements CatalogCategorySearchRequestInterface
{
    /**
     * @var Category
     */
    protected $category;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="3", max="100")
     *
     * @var string
     */
    protected $searchString;

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
    public function setCategory(Category $category): CatalogCategorySearchRequestInterface
    {
        $this->category = $category;

        return $this;
    }
}
