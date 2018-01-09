<?php

namespace FourPaws\SapBundle\Dto\In\Prices;

use JMS\Serializer\Annotation as Serializer;

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
     * Содержит розничную цену торгового предложения на момент выгрузки товара.
     * Система должна сбросить региональную цену и установить в указанном регионе глобальную цену.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Price_Retail")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $retailPrice = 0;

    /**
     * Цена по акции
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Price_Action")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $actionPrice = 0;

    /**
     * Тип цены
     *
     * Содержит тип цены. Тип поля – единственный выбор из значений:
     * Пусто – розничная цена;
     * VKA0 – цена по акции «Рекламная цена»;
     * ZRBT – цена по акции «Скидка на товар»
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Cond_For_Action")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $priceType = '';


    /**
     * Размер скидки на товар
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Cond_Value")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $discountValue = 0;

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
    public function getRetailPrice(): float
    {
        return $this->retailPrice;
    }

    /**
     * @param float $retailPrice
     *
     * @return Item
     */
    public function setRetailPrice(float $retailPrice): Item
    {
        $this->retailPrice = $retailPrice;
        return $this;
    }

    /**
     * @return float
     */
    public function getActionPrice(): float
    {
        return $this->actionPrice;
    }

    /**
     * @param float $actionPrice
     *
     * @return Item
     */
    public function setActionPrice(float $actionPrice): Item
    {
        $this->actionPrice = $actionPrice;
        return $this;
    }

    /**
     * @return string
     */
    public function getPriceType(): string
    {
        return $this->priceType;
    }

    /**
     * @param string $priceType
     *
     * @return Item
     */
    public function setPriceType(string $priceType): Item
    {
        $this->priceType = $priceType;
        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountValue(): float
    {
        return $this->discountValue;
    }

    /**
     * @param float $discountValue
     *
     * @return Item
     */
    public function setDiscountValue(string $discountValue): Item
    {
        $this->discountValue = $discountValue;
        return $this;
    }
}
