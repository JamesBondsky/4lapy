<?php

namespace FourPaws\Catalog\Model;

use FourPaws\BitrixOrm\Model\BitrixArrayItemBase;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

class Price extends BitrixArrayItemBase
{
    /**
     * @var int
     */
    protected $ELEMENT_ID = 0;

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $REGION_ID = '';

    /**
     * @var float
     * @Type("float")
     * @Groups({"elastic"})
     */
    protected $PRICE = 0.0;

    /**
     * @var string
     * @Type("string")
     * @Groups({"elastic"})
     */
    protected $CURRENCY = '';

    /**
     * @return int
     */
    public function getElementId(): int
    {
        return (int)$this->ELEMENT_ID;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function withElementId(int $id)
    {
        $this->ELEMENT_ID = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegionId(): string
    {
        return (string)$this->REGION_ID;
    }

    /**
     * @param string $regionId
     *
     * @return $this
     */
    public function withRegionId(string $regionId)
    {
        $this->REGION_ID = $regionId;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return (float)$this->PRICE;
    }

    /**
     * @param float $price
     *
     * @return $this
     */
    public function withPrice(float $price)
    {
        $this->PRICE = $price;

        return $this;
    }

    /**
     * @return float
     */
    private function getOldPrice(): float
    {
        //TODO Реализовать динамический запрос цены со скидкой
        return $this->getPrice();
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->CURRENCY;
    }

    /**
     * @param string $currency
     *
     * @return $this
     */
    public function withCurrency(string $currency)
    {
        $this->CURRENCY = $currency;

        return $this;
    }

    /**
     * Возвращает форматированную цену с учётом скидки
     *
     * @param bool $onlyNumber
     * @param bool $withoutFormat
     *
     * @return string
     */
    public function getFormattedPrice(bool $onlyNumber = false, $withoutFormat = false): string
    {
        return $this->doFormat($this->getPrice(), $onlyNumber, $withoutFormat);
    }

    public function getFormattedOldPrice(bool $onlyNumber = false, $withoutFormat = false): string
    {
        return $this->doFormat($this->getOldPrice(), $onlyNumber, $withoutFormat);
    }

    /**
     * @param $value
     * @param bool $onlyNumber
     * @param bool $withoutFormat
     *
     * @return string
     */
    protected function doFormat($value, bool $onlyNumber, bool $withoutFormat): string
    {
        return (string)SaleFormatCurrency($value, $this->getCurrency(), $onlyNumber, $withoutFormat);
    }
}
