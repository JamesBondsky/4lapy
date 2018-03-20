<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

/**
 * ОбъектГород
 * Class City
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class City
{
    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Type("string")
     * @var string
     */
    protected $id = '';

    /**
     * @Serializer\SerializedName("title")
     * @Serializer\Type("string")
     * @var string
     */
    protected $title = '';

    /**
     * @Serializer\SerializedName("has_metro")
     * @Serializer\Type("bool")
     * @var bool
     */
    protected $hasMetro = false;

    /**
     * @Serializer\SerializedName("path")
     * @Serializer\Type("array<string>")
     * @var array
     */
    protected $path = [];

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return City
     */
    public function setId(string $id): City
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return City
     */
    public function setTitle(string $title): City
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHasMetro(): bool
    {
        return $this->hasMetro;
    }

    /**
     * @param bool $hasMetro
     * @return City
     */
    public function setHasMetro(bool $hasMetro): City
    {
        $this->hasMetro = $hasMetro;
        return $this;
    }

    /**
     * @return array
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @param array $path
     * @return City
     */
    public function setPath(array $path): City
    {
        $this->path = $path;
        return $this;
    }
}
