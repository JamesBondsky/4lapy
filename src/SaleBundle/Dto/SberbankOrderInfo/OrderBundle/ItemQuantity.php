<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\SberbankOrderInfo\OrderBundle;

use JMS\Serializer\Annotation as Serializer;

class ItemQuantity
{
    /**
     * @var float
     *
     * @Serializer\SerializedName("value")
     * @Serializer\Type("float")
     */
    protected $value = 0;

    /**
     * @var string
     *
     * @Serializer\SerializedName("measure")
     * @Serializer\Type("string")
     */
    protected $measure = '';

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @param float $value
     * @return ItemQuantity
     */
    public function setValue(float $value): ItemQuantity
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getMeasure(): string
    {
        return $this->measure;
    }

    /**
     * @param string $measure
     * @return ItemQuantity
     */
    public function setMeasure(string $measure): ItemQuantity
    {
        $this->measure = $measure;

        return $this;
    }
}
