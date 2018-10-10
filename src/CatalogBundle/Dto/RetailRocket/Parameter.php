<?php

namespace FourPaws\CatalogBundle\Dto\RetailRocket;

use FourPaws\CatalogBundle\Dto\YmlOfferParameterInterface;
use JMS\Serializer\Annotation as Serializer;

class Parameter implements YmlOfferParameterInterface
{
    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Required()
     *
     * @var string
     */
    protected $name;

    /**
     * @Serializer\XmlValue(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $value;

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
     * @return Parameter
     */
    public function setName(string $name): Parameter
    {
        $this->name = $name;

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
     * @return Parameter
     */
    public function setValue(string $value): Parameter
    {
        $this->value = $value;

        return $this;
    }
}
