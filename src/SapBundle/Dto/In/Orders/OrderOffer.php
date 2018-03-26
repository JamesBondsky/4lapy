<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\Orders;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Product
 * @package FourPaws\SapBundle\Dto\Out\Orders
 * @Serializer\XmlRoot(name="Item")
 */
class OrderOffer
{
    /**
     * Содержит номер позиции торгового предложения в заказе
     * 6-значный цифровой код, формат: 000010
     *
     * В нашем случае приводится к числу
     * Минимально - 1
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Pos_NR")
     * @Serializer\Type("sap_position")
     *
     * @var int
     */
    protected $position;

    /**
     * УИД торгового предложения
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Matnr")
     *
     * @var string
     */
    protected $offerXmlId = '';

    /**
     * Количество
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Qty")
     *
     * @var int
     */
    protected $quantity = 0;

    /**
     * Код единицы измерения торгового предложения
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Qty_UOM")
     *
     * @var string
     */
    protected $unitOfMeasureCode = '';

    /**
     * Цена за единицу товара в заказе
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Price")
     *
     * @var float
     */
    protected $unitPrice = 0;
    
    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }
    
    /**
     * @param int $position
     *
     * @return OrderOffer
     */
    public function setPosition(int $position): OrderOffer
    {
        $this->position = $position;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getOfferXmlId(): string
    {
        return $this->offerXmlId;
    }
    
    /**
     * @param string $offerXmlId
     *
     * @return OrderOffer
     */
    public function setOfferXmlId(string $offerXmlId): OrderOffer
    {
        $this->offerXmlId = $offerXmlId;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }
    
    /**
     * @param int $quantity
     *
     * @return OrderOffer
     */
    public function setQuantity(int $quantity): OrderOffer
    {
        $this->quantity = $quantity;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getUnitOfMeasureCode(): string
    {
        return $this->unitOfMeasureCode;
    }
    
    /**
     * @param string $unitOfMeasureCode
     *
     * @return OrderOffer
     */
    public function setUnitOfMeasureCode(string $unitOfMeasureCode): OrderOffer
    {
        $this->unitOfMeasureCode = $unitOfMeasureCode;
        return $this;
    }
    
    /**
     * @return float
     */
    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }
    
    /**
     * @param float $unitPrice
     *
     * @return OrderOffer
     */
    public function setUnitPrice(float $unitPrice): OrderOffer
    {
        $this->unitPrice = $unitPrice;
        return $this;
    }
}
