<?php

namespace FourPaws\CatalogBundle\Dto\Dostavista;

use Doctrine\Common\Annotations\Annotation\Required;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Category
 *
 * @package FourPaws\CatalogBundle\Dto\Dostavista
 *
 * @Serializer\XmlRoot("category")
 */
class Category
{
    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Required()
     *
     * @var int
     */
    protected $id;

    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SkipWhenEmpty()
     *
     * @var int
     */
    protected $parentId;

    /**
     * @Serializer\XmlValue(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Category
     */
    public function setId(int $id): Category
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     *
     * @return Category
     */
    public function setParentId(?int $parentId): Category
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Category
     */
    public function setName(string $name): Category
    {
        $this->name = $name;

        return $this;
    }
}
