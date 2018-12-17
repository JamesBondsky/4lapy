<?php

namespace FourPaws\CatalogBundle\Dto\Yandex;

use Doctrine\Common\Annotations\Annotation\Required;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Param
 *
 * @package FourPaws\CatalogBundle\Dto\Yandex
 *
 * @Serializer\XmlRoot("param")
 */
class Param
{
    /**
     * @Required()
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * @Required()
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $unit;

    /**
     * @Required()
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
     * @return Param
     */
    public function setName(string $name): Param
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     * @return Param
     */
    public function setUnit(string $unit): Param
    {
        $this->unit = $unit;

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
     * @return Param
     */
    public function setValue(string $value): Param
    {
        $this->value = $value;
        return $this;
    }
}
