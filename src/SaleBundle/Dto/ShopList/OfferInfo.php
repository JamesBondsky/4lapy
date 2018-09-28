<?php

namespace FourPaws\SaleBundle\Dto\ShopList;

use JMS\Serializer\Annotation as Serializer;

class OfferInfo
{
    /**
     * @var string
     *
     * @Serializer\SerializedName("name")
     * @Serializer\Type("string")
     */
    protected $name;

    /**
     * @var int
     *
     * @Serializer\SerializedName("weight")
     * @Serializer\Type("int")
     */
    protected $weight;

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
     * @return OfferInfo
     */
    public function setName(string $name): OfferInfo
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     *
     * @return OfferInfo
     */
    public function setWeight(int $weight): OfferInfo
    {
        $this->weight = $weight;

        return $this;
    }
}
