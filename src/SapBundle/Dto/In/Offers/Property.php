<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\Offers;

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
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Offers\PropertyValue>")
     *
     * @var Collection|PropertyValue[]
     */
    protected $values;

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
     * @return Property
     */
    public function setName(string $name): Property
    {
        $this->name = $name;
        return $this;
    }

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
     * @return Property
     */
    public function setCode(string $code): Property
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return Collection|PropertyValue[]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param Collection|PropertyValue[] $values
     *
     * @return Property
     */
    public function setValues($values): Property
    {
        $this->values = $values;
        return $this;
    }
}
