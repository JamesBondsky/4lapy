<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\Offers;

use JMS\Serializer\Annotation as Serializer;

class PropertyValue
{
    /**
     * Код значения признака товара
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Val")
     *
     * @var string
     */
    protected $code;

    /**
     * Название значения признака товара
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Descr")
     *
     * @var string
     */
    protected $name;

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return PropertyValue
     */
    public function setCode(string $code): PropertyValue
    {
        $this->code = $code;
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
     * @return PropertyValue
     */
    public function setName(string $name): PropertyValue
    {
        $this->name = $name;
        return $this;
    }
}
