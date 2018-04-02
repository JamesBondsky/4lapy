<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\ConfirmPayment;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Item
 * @package FourPaws\SapBundle\Dto\In
 */
class Item
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
     * Содержит розничную цену с НДС за единицу товара в заказе. Число десятичных знаков после запятой – 3
     * Система передает значение поля в Атол
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Price")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $price = 0;

    /**
     * Содержит стоимость торгового предложения с учетом его количества в заказе. Число десятичных знаков после запятой – 3.
     * Система передает значение поля в Атол
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Gross")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $sumPrice = 0;

    /**
     * Содержит величину НДС для единицы товара в заказе. Число десятичных знаков после запятой – 3.
     * Система передает значение поля в Атол
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("VAT2")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $vatPrice = 0;

    /**
     * Содержит ставку НДС для единицы товара в заказе. Возможные значения: 10, 18.
     * Система передает значение поля в Атол
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("VATCode2")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $vatRate = 0;

    /**
     * Содержит название торгового предложения
     * Система передает значение поля в Атол.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("ARKTX")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $offerName = '';

    /**
     * Содержит количество единиц торгового предложения в заказе.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Fact")
     * @Serializer\Type("int")
     *
     * @var string
     */
    protected $quantity = 0;

    /**
     * @return int
     */
    public function getOfferXmlId(): int
    {
        return $this->offerXmlId;
    }

    /**
     * @param int $offerXmlId
     *
     * @return Item
     */
    public function setOfferXmlId(int $offerXmlId): Item
    {
        $this->offerXmlId = $offerXmlId;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return Item
     */
    public function setPrice(float $price): Item
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getSumPrice(): float
    {
        return $this->sumPrice;
    }

    /**
     * @param float $sumPrice
     * @return Item
     */
    public function setSumPrice(float $sumPrice): Item
    {
        $this->sumPrice = $sumPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getVatPrice(): float
    {
        return $this->vatPrice;
    }

    /**
     * @param float $vatPrice
     * @return Item
     */
    public function setVatPrice(float $vatPrice): Item
    {
        $this->vatPrice = $vatPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getVatRate(): float
    {
        return $this->vatRate;
    }

    /**
     * @param float $vatRate
     * @return Item
     */
    public function setVatRate(float $vatRate): Item
    {
        $this->vatRate = $vatRate;

        return $this;
    }

    /**
     * @return string
     */
    public function getOfferName(): string
    {
        return $this->offerName;
    }

    /**
     * @param string $offerName
     * @return Item
     */
    public function setOfferName(string $offerName): Item
    {
        $this->offerName = $offerName;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuantity(): string
    {
        return $this->quantity;
    }

    /**
     * @param string $quantity
     *
     * @return $this
     */
    public function setQuantity(string $quantity): Item
    {
        $this->quantity = $quantity;

        return $this;
    }
}
