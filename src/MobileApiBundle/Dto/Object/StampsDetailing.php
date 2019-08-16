<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

/**
 * ОбъектДетализацииМарок
 * Class StampsDetailing
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class StampsDetailing
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
     * @Serializer\SerializedName("value")
     * @Serializer\Type("string")
     * @var string
     */
    protected $value = '';

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
     * @return StampsDetailing
     */
    public function setId(string $id): StampsDetailing
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
     *
     * @return StampsDetailing
     */
    public function setTitle(string $title): StampsDetailing
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return StampsDetailing
     */
    public function setValue(string $value): StampsDetailing
    {
        $this->value = (int)$value;
        return $this;
    }
}
