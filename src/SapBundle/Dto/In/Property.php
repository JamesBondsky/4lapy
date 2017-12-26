<?php

namespace FourPaws\SapBundle\Dto\In;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

class Property
{
    /**
     * Название признака
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Description")
     *
     * @var string
     */
    protected $name = '';

    /**
     * Код признака товара
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Name")
     *
     * @var string
     */
    protected $code = '';

    /**
     * Значения признаков
     *
     * @Serializer\XmlList(inline=true, entry="Value")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\PropertyValue>")
     *
     * @var Collection|PropertyValue[]
     */
    protected $values;
}
