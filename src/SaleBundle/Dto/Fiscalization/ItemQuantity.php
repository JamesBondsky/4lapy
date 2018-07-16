<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\Fiscalization;

use JMS\Serializer\Annotation as Serializer;

class ItemQuantity
{
    /**
     * @var int
     *
     * @Serializer\SerializedName("value")
     * @Serializer\Type("int")
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
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     * @return ItemQuantity
     */
    public function setValue(int $value): ItemQuantity
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