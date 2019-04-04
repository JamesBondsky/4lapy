<?php

namespace FourPaws\CatalogBundle\Dto\Dostavista;

use Doctrine\Common\Annotations\Annotation\Required;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Residue
 *
 * @package FourPaws\CatalogBundle\Dto\Dostavista
 *
 * @Serializer\XmlRoot("residue")
 */
class Residue
{
    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Required()
     *
     * @var string
     */
    protected $merchantId;

    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Required()
     *
     * @var string
     */
    protected $amount;

    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Required()
     *
     * @var string
     */
    protected $price;

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     * @return Residue
     */
    public function setMerchantId(string $merchantId): Residue
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     * @return Residue
     */
    public function setAmount(string $amount): Residue
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @param string $price
     * @return Residue
     */
    public function setPrice(string $price): Residue
    {
        $this->price = $price;

        return $this;
    }
}
