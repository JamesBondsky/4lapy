<?php

namespace FourPaws\SapBundle\Dto\In\DcStock;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class StockItem
 * Класс для элемента <STOCKITEMS /> XML импорта остатков по РЦ
 *
 * @package FourPaws\SapBundle\Dto\In
 * @Serializer\XmlRoot("STOCKITEMS")
  */
class StockItem
{
    /**
     * УИД торгового предложения
     *
     * @Serializer\XmlElement()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("MATNR")
     *
     * @var int
     */
    protected $offerXmlId = 0;

    /**
     * Код завода или поставщика
     *
     * @Serializer\XmlElement()
     * @Serializer\SerializedName("LIFNR")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $plantCode = '';

    /**
     * Тип запаса
     *
     * @Serializer\XmlElement()
     * @Serializer\SerializedName("ATTRB")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $stockType = '';

    /**
     * Остатки
     *
     * @Serializer\XmlElement()
     * @Serializer\SerializedName("QUANT")
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
     * @param string $offerXmlId
     * @return StockItem
     */
    public function setOfferXmlId(string $offerXmlId): StockItem
    {
        $this->offerXmlId = $offerXmlId;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlantCode(): string
    {
        return $this->plantCode;
    }

    /**
     * @param string $plantCode
     *
     * @return StockItem
     */
    public function setPlantCode(string $plantCode): StockItem
    {
        $this->plantCode = $plantCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getStockType(): string
    {
        return $this->stockType;
    }

    /**
     * @param string $stockType
     * @return StockItem
     */
    public function setStockType(string $stockType): StockItem
    {
        $this->stockType = $stockType;
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
     * @return StockItem
     */
    public function setStockValue(float $stockValue): StockItem
    {
        $this->stockValue = $stockValue;
        return $this;
    }
}
