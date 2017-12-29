<?php

namespace FourPaws\SapBundle\Dto\In\Offers;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class UnitOfMeasurement
 * @package FourPaws\SapBundle\Dto\In
 * @Serializer\XmlRoot("UOM")
 */
class UnitOfMeasurement
{
    /**
     * Содержит код альтернативной единицы измерения.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Code")
     * @Serializer\Type("string")
     *
     * @internal
     * @var string
     */
    protected $alternativeUnitCode = '';

    /**
     * Содержит название альтернативной единицы измерения.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("SName")
     * @Serializer\Type("string")
     *
     * @internal
     * @var string
     */
    protected $alternativeUnitName = '';

    /**
     * Содержит числитель для пересчета альтернативной единицы изменения в базовую единицу измерения.
     * Поле игнорируется Сайтом
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("UMREZ")
     * @Serializer\Type("int")
     *
     * @internal
     * @var int
     */
    protected $alternativeUnitNumerator = 0;

    /**
     * Содержит знаменатель для пересчета альтернативной единицы изменения в базовую единицу измерения.
     * Поле игнорируется Сайтом
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("UMREN")
     * @Serializer\Type("int")
     *
     * @internal
     * @var int
     */
    protected $alternativeUnitDenominator = 0;

    /**
     * Код единицы изменения веса
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("WeightUOMCode")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $weightUnitCode = '';

    /**
     * Название единицы измерения веса
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("WeightUOMName")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $weightUnitName = '';

    /**
     * Вес брутто
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("WeightBrutto")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $grossWeight = 0;

    /**
     * Код единицы измерения размера
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("SizeUOMCode")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $sizeUnitCode = '';

    /**
     * Код единицы измерения размера
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("SizeUOMName")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $sizeUnitName = '';

    /**
     * Длина
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Length")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $length = 0;

    /**
     * Ширина
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Width")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $width = 0;

    /**
     * Высота
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Height")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $height = 0;

    /**
     * Код единицы измерения объема
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("VolumeUOMCode")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $volumeUnitCode = '';

    /**
     * Название единицы измерения объема
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("VolumeUOMName")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $volumeUnitName = '';

    /**
     * Объем
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Height")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $volume = 0;

    /**
     * Штрих-коды
     *
     * @Serializer\XmlList(inline=true, entry="BC")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Offers\BarCode>")
     *
     * @var BarCode[]|Collection
     */
    protected $barCodes;
}
