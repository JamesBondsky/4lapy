<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\Offers;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class UnitOfMeasurement
 *
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

    /**
     * @return string
     */
    public function getAlternativeUnitCode(): string
    {
        return $this->alternativeUnitCode;
    }

    /**
     * @param string $alternativeUnitCode
     *
     * @return UnitOfMeasurement
     */
    public function setAlternativeUnitCode(string $alternativeUnitCode): UnitOfMeasurement
    {
        $this->alternativeUnitCode = $alternativeUnitCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlternativeUnitName(): string
    {
        return $this->alternativeUnitName;
    }

    /**
     * @param string $alternativeUnitName
     *
     * @return UnitOfMeasurement
     */
    public function setAlternativeUnitName(string $alternativeUnitName): UnitOfMeasurement
    {
        $this->alternativeUnitName = $alternativeUnitName;
        return $this;
    }

    /**
     * @return int
     */
    public function getAlternativeUnitNumerator(): int
    {
        return $this->alternativeUnitNumerator;
    }

    /**
     * @param int $alternativeUnitNumerator
     *
     * @return UnitOfMeasurement
     */
    public function setAlternativeUnitNumerator(int $alternativeUnitNumerator): UnitOfMeasurement
    {
        $this->alternativeUnitNumerator = $alternativeUnitNumerator;
        return $this;
    }

    /**
     * @return int
     */
    public function getAlternativeUnitDenominator(): int
    {
        return $this->alternativeUnitDenominator;
    }

    /**
     * @param int $alternativeUnitDenominator
     *
     * @return UnitOfMeasurement
     */
    public function setAlternativeUnitDenominator(int $alternativeUnitDenominator): UnitOfMeasurement
    {
        $this->alternativeUnitDenominator = $alternativeUnitDenominator;
        return $this;
    }

    /**
     * @return string
     */
    public function getWeightUnitCode(): string
    {
        return $this->weightUnitCode;
    }

    /**
     * @param string $weightUnitCode
     *
     * @return UnitOfMeasurement
     */
    public function setWeightUnitCode(string $weightUnitCode): UnitOfMeasurement
    {
        $this->weightUnitCode = $weightUnitCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getWeightUnitName(): string
    {
        return $this->weightUnitName;
    }

    /**
     * @param string $weightUnitName
     *
     * @return UnitOfMeasurement
     */
    public function setWeightUnitName(string $weightUnitName): UnitOfMeasurement
    {
        $this->weightUnitName = $weightUnitName;
        return $this;
    }

    /**
     * @return float
     */
    public function getGrossWeight(): float
    {
        return $this->grossWeight;
    }

    /**
     * @param float $grossWeight
     *
     * @return UnitOfMeasurement
     */
    public function setGrossWeight(float $grossWeight): UnitOfMeasurement
    {
        $this->grossWeight = $grossWeight;
        return $this;
    }

    /**
     * @return string
     */
    public function getSizeUnitCode(): string
    {
        return $this->sizeUnitCode;
    }

    /**
     * @param string $sizeUnitCode
     *
     * @return UnitOfMeasurement
     */
    public function setSizeUnitCode(string $sizeUnitCode): UnitOfMeasurement
    {
        $this->sizeUnitCode = $sizeUnitCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getSizeUnitName(): string
    {
        return $this->sizeUnitName;
    }

    /**
     * @param string $sizeUnitName
     *
     * @return UnitOfMeasurement
     */
    public function setSizeUnitName(string $sizeUnitName): UnitOfMeasurement
    {
        $this->sizeUnitName = $sizeUnitName;
        return $this;
    }

    /**
     * @return float
     */
    public function getLength(): float
    {
        return $this->length;
    }

    /**
     * @param float $length
     *
     * @return UnitOfMeasurement
     */
    public function setLength(float $length): UnitOfMeasurement
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return float
     */
    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * @param float $width
     *
     * @return UnitOfMeasurement
     */
    public function setWidth(float $width): UnitOfMeasurement
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return float
     */
    public function getHeight(): float
    {
        return $this->height;
    }

    /**
     * @param float $height
     *
     * @return UnitOfMeasurement
     */
    public function setHeight(float $height): UnitOfMeasurement
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return string
     */
    public function getVolumeUnitCode(): string
    {
        return $this->volumeUnitCode;
    }

    /**
     * @param string $volumeUnitCode
     *
     * @return UnitOfMeasurement
     */
    public function setVolumeUnitCode(string $volumeUnitCode): UnitOfMeasurement
    {
        $this->volumeUnitCode = $volumeUnitCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getVolumeUnitName(): string
    {
        return $this->volumeUnitName;
    }

    /**
     * @param string $volumeUnitName
     *
     * @return UnitOfMeasurement
     */
    public function setVolumeUnitName(string $volumeUnitName): UnitOfMeasurement
    {
        $this->volumeUnitName = $volumeUnitName;
        return $this;
    }

    /**
     * @return float
     */
    public function getVolume(): float
    {
        return $this->volume;
    }

    /**
     * @param float $volume
     *
     * @return UnitOfMeasurement
     */
    public function setVolume(float $volume): UnitOfMeasurement
    {
        $this->volume = $volume;
        return $this;
    }

    /**
     * @return BarCode[]|Collection
     */
    public function getBarCodes()
    {
        return $this->barCodes;
    }

    /**
     * @param BarCode[]|Collection $barCodes
     *
     * @return UnitOfMeasurement
     */
    public function setBarCodes($barCodes)
    {
        $this->barCodes = $barCodes;
        return $this;
    }
}
