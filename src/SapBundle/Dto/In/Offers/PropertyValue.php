<?php

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
}
