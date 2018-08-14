<?php

namespace FourPaws\CatalogBundle\Dto\Yandex;

use Doctrine\Common\Annotations\Annotation\Required;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Currency
 *
 * @package FourPaws\CatalogBundle\Dto\Yandex
 *
 * @Serializer\XmlRoot("currency")
 */
class Currency
{
    /**
     * @Required()
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $id;

    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $rate;

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
     * @return Currency
     */
    public function setId(string $id): Currency
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return float
     */
    public function getRate(): float
    {
        return $this->rate;
    }

    /**
     * @param float $rate
     *
     * @return $this
     */
    public function setRate(float $rate): Currency
    {
        $this->rate = $rate;

        return $this;
    }

}
