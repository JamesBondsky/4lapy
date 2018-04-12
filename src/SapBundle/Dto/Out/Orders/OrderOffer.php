<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\Out\Orders;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Product
 * @package FourPaws\SapBundle\Dto\Out\Orders
 * @Serializer\XmlRoot(name="Item")
 */
class OrderOffer
{
    public const DEFAULT_PROVIDER_POINT = 'DC01';

    /**
     * Содержит номер позиции торгового предложения в заказе
     * 6-значный цифровой код, формат: 000010
     *
     * В нашем случае приводится к числу
     * Минимально - 1
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("PosNumber")
     * @Serializer\Type("sap_position")
     *
     * @var int
     */
    protected $position = 1;

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
     * @Serializer\SerializedName("UOM")
     *
     * @var string
     */
    protected $unitOfMeasureCode = '';

    /**
     * Код склада/магазина откуда будет забран заказ
     * Или откуда повезут непосредственно заказ
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Plant")
     *
     * @var string
     */
    protected $deliveryFromPoint = '';

    /**
     * Код склада/поставщика откуда будет произведена отгрузка по заказу
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UnloadingPoint")
     *
     * @var string
     */
    protected $deliveryShipmentPoint = '';

    /**
     * Цена за единицу товара в заказе
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Amount")
     *
     * @var float
     */
    protected $unitPrice = 0;

    /**
     * Начислять ли бонусы по позиции товара
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("signcharge")
     *
     * @var bool
     */
    protected $chargeBonus = 0;

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
     * @return string
     */
    public function getDeliveryFromPoint(): string
    {
        return $this->deliveryFromPoint;
    }
    
    /**
     * @param string $deliveryFromPoint
     *
     * @return OrderOffer
     */
    public function setDeliveryFromPoint(string $deliveryFromPoint): OrderOffer
    {
        $this->deliveryFromPoint = $deliveryFromPoint;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getDeliveryShipmentPoint(): string
    {
        return $this->deliveryShipmentPoint;
    }
    
    /**
     * @param string $deliveryShipmentPoint
     *
     * @return OrderOffer
     */
    public function setDeliveryShipmentPoint(string $deliveryShipmentPoint): OrderOffer
    {
        $this->deliveryShipmentPoint = $deliveryShipmentPoint;

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
    
    /**
     * @return bool
     */
    public function isChargeBonus(): bool
    {
        return $this->chargeBonus === 1;
    }
    
    /**
     * @param bool $chargeBonus
     *
     * @return OrderOffer
     */
    public function setChargeBonus(bool $chargeBonus): OrderOffer
    {
        $this->chargeBonus = $chargeBonus === true ? 1 : 0;

        return $this;
    }
}
