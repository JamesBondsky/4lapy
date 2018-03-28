<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog;

use JMS\Serializer\Annotation as Serializer;

class FilterVariant
{
    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Type("string")
     * @var string
     */
    protected $id;

    /**
     * @Serializer\SerializedName("name")
     * @Serializer\Type("string")
     * @var string
     */
    protected $name;

    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return FilterVariant
     */
    public function setId(string $id): FilterVariant
    {
        $this->id = $id;
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
     * @return FilterVariant
     */
    public function setName(string $name): FilterVariant
    {
        $this->name = $name;
        return $this;
    }
}
