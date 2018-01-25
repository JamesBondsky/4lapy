<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * КатегорияКаталога
 * Class CatalogCategory
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class CatalogCategory
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     * @var string
     */
    protected $title = '';

    /**
     * absolute url
     *
     * @Assert\Url()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("picture")
     * @var string
     */
    protected $picture;

    /**
     * @Serializer\SerializedName("has_child")
     * @Serializer\Type("bool")
     * @var bool
     */
    protected $hasChild = false;
    /**
     * @Serializer\SerializedName("child")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\CatalogCategory>")
     * @var CatalogCategory[]
     */
    protected $child = [];

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return CatalogCategory
     */
    public function setTitle(string $title): CatalogCategory
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getPicture(): string
    {
        return $this->picture;
    }

    /**
     * @param string $picture
     *
     * @return CatalogCategory
     */
    public function setPicture(string $picture): CatalogCategory
    {
        $this->picture = $picture;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHasChild(): bool
    {
        return $this->hasChild;
    }

    /**
     * @param bool $hasChild
     *
     * @return CatalogCategory
     */
    public function setHasChild(bool $hasChild): CatalogCategory
    {
        $this->hasChild = $hasChild;
        return $this;
    }

    /**
     * @return CatalogCategory[]
     */
    public function getChild(): array
    {
        return $this->child;
    }

    /**
     * @param CatalogCategory[] $child
     *
     * @return CatalogCategory
     */
    public function setChild(array $child): CatalogCategory
    {
        $this->child = $child;
        return $this;
    }

    /**
     * @param CatalogCategory $catalogCategory
     *
     * @return CatalogCategory
     */
    public function addChildren(CatalogCategory $catalogCategory): CatalogCategory
    {
        if (!\in_array($catalogCategory, $this->child, true)) {
            $this->child[] = $catalogCategory;
        }
        return $this;
    }

    /**
     * @param CatalogCategory $catalogCategory
     *
     * @return CatalogCategory
     */
    public function removeChildren(CatalogCategory $catalogCategory): CatalogCategory
    {
        $key = array_search($catalogCategory, $this->child, true);
        if ($key !== false) {
            unset($this->child[$key]);
        }
        return $this;
    }
}
