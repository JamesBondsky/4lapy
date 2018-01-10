<?php

namespace FourPaws\SapBundle\Dto\In\Stock;

use JMS\Serializer\Annotation as Serializer;

class Dc
{
    /**
     * УИД торгового предложения
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Matnr")
     *
     * @var int
     */
    protected $offerXmlId = 0;

    /**
     * Код РЦ
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Werks_DC")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $werksCode = '';


    /**
     * Остатки
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Stock")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $stockValue = 0;

    /**
     * @return string
     */
    public function getOfferXmlId(): string
    {
        return $this->offerXmlId;
    }

    /**
     * @param string $werksCode
     *
     * @return Dc
     */
    public function setOfferXmlId(string $offerXmlId): Dc
    {
        $this->offerXmlId = $offerXmlId;
        return $this;
    }

    /**
     * @return string
     */
    public function getWerksCode(): string
    {
        return $this->werksCode;
    }

    /**
     * @param string $werksCode
     *
     * @return Dc
     */
    public function setWerksCode(string $werksCode): Dc
    {
        $this->werksCode = $werksCode;
        return $this;
    }

    /**
     * @return float
     */
    public function getStockValue(): float
    {
        return $this->stockValue;
    }

    /**
     * @param float $stockValue
     *
     * @return Dc
     */
    public function setStockValue(float $stockValue): Dc
    {
        $this->stockValue = $stockValue;
        return $this;
    }
}
