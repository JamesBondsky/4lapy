<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\CatalogCategory;
use JMS\Serializer\Annotation as Serializer;

class CategoriesResponse
{
    /**
     * @Serializer\SerializedName("categories")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\CatalogCategory>")
     * @var CatalogCategory[]
     */
    protected $categories = [];

    /**
     * @return CatalogCategory[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param CatalogCategory[] $categories
     */
    public function setCategories(array $categories)
    {
        $this->categories = $categories;
    }

    /**
     * @param CatalogCategory $catalogCategory
     *
     * @return CategoriesResponse
     */
    public function addCategory(CatalogCategory $catalogCategory): CategoriesResponse
    {
        if (!\in_array($catalogCategory, $this->categories, true)) {
            $this->categories[] = $catalogCategory;
        }
        return $this;
    }

    /**
     * @param CatalogCategory $catalogCategory
     *
     * @return CategoriesResponse
     */
    public function removeCategory(CatalogCategory $catalogCategory): CategoriesResponse
    {
        $key = array_search($catalogCategory, $this->categories, true);
        if ($key !== false) {
            unset($this->categories[$key]);
        }
        return $this;
    }
}
