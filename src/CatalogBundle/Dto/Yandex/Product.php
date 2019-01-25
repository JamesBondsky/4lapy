<?php

namespace FourPaws\CatalogBundle\Dto\Yandex;

use Doctrine\Common\Annotations\Annotation\Required;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Product
 *
 * @package FourPaws\CatalogBundle\Dto\Yandex
 *
 * @Serializer\XmlRoot("product")
 */
class Product
{
    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Required()
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $offerId;

    /**
     * @return string
     */
    public function getOfferId(): string
    {
        return $this->offerId;
    }

    /**
     * @param string $offerId
     * @return Product
     */
    public function setOfferId(string $offerId): Product
    {
        $this->offerId = $offerId;

        return $this;
    }
}
