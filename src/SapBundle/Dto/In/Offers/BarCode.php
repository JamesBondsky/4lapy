<?php

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
}
