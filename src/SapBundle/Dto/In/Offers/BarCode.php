<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\Offers;

use JMS\Serializer\Annotation as Serializer;

class BarCode
{
    /**
     * Содержит штрих-код торгового предложения в формате ЕАN-13 для единицы измерения
     * указанной в группе данных об единице измерения UOM‎.
     * Для составного товара может быть несколько штрих-кодов, которые выгружаются с разделителем «,».
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("EAN")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $value;

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return BarCode
     */
    public function setValue(string $value): BarCode
    {
        $this->value = $value;
        return $this;
    }
}
