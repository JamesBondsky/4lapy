<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Response;

use Doctrine\Common\Collections\Collection;
use FourPaws\MobileApiBundle\Dto\Object\CatalogCategory;
use JMS\Serializer\Annotation as Serializer;

class CategoriesResponse
{
    /**
     * @Serializer\SerializedName("categories")
     * @Serializer\Type("ArrayCollection<FourPaws\MobileApiBundle\Dto\Object\CatalogCategory>")
     * @var CatalogCategory[]
     */
    protected $categories;

    public function __construct(Collection $categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return CatalogCategory[]|Collection
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @param CatalogCategory[]|Collection $categories
     */
    public function setCategories(Collection $categories)
    {
        $this->categories = $categories;
    }

    /**
     * @param CatalogCategory $catalogCategory
     *
     * @return bool
     */
    public function addCategory(CatalogCategory $catalogCategory): bool
    {
        return $this->categories->add($catalogCategory);
    }

    /**
     * @param CatalogCategory $catalogCategory
     *
     * @return bool
     */
    public function removeCategory(CatalogCategory $catalogCategory): bool
    {
        return $this->categories->removeElement($catalogCategory);
    }
}
