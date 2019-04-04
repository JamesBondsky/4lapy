<?php

namespace FourPaws\CatalogBundle\Dto\Dostavista;

use Doctrine\Common\Annotations\Annotation\Required;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Weight
 *
 * @package FourPaws\CatalogBundle\Dto\Dostavista
 *
 * @Serializer\XmlRoot("weight")
 */
class Weight
{
    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("unit-id")
     *
     * @var string
     */
    protected $unitId = 'kg';

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
    public function getUnitId(): string
    {
        return $this->unitId;
    }

    /**
     * @param string $unitId
     * @return Weight
     */
    public function setUnitId(string $unitId): Weight
    {
        $this->unitId = $unitId;

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
     * @return Weight
     */
    public function setValue(string $value): Weight
    {
        $this->value = $value;

        return $this;
    }
}
