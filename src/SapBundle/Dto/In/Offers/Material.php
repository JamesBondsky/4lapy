<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\Offers;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Material
 *
 * @package FourPaws\SapBundle\Dto\In
 * @Serializer\XmlRoot("Mat")
 * @todo    Проверить отсуствующие поля - ответсвенный Николай Кудряшов
 */
class Material
{
    const DEFAULT_BASE_UNIT_OF_MEASUREMENT_CODE = 'ST';
    const DEFAULT_BASE_UNIT_OF_MEASUREMENT_NAME = 'шт';

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
     * Старый номер материала
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Old_Number")
     *
     * @var string
     */
    protected $oldOfferXmlId = '';

    /**
     * Наименование торгового предложения
     * Содержит название торгового предложения с указанием фасовки/размера/цвета/вкуса.
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Name")
     * @Serializer\XmlAttribute()
     *
     * @var string
     */
    protected $offerName = '';

    /**
     * @todo Проверить наличие в боевой XML - в тестовой отсуствует
     * Наименование составного товара
     * Содержит название составного товара.
     * Поле необязательно для заполнения.
     * Может быть заполнено только для одного торгового предложения составного товара.
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Name2")
     * @Serializer\XmlAttribute()
     *
     * @var string
     */
    protected $productName = '';

    /**
     * Код базовой единицы измерения
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("BaseUOM")
     *
     * @var string
     */
    protected $basicUnitOfMeasurementCode = Material::DEFAULT_BASE_UNIT_OF_MEASUREMENT_CODE;

    /**
     * Базовая единица изменения
     * Содержит единицу измерения торгового предложения.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("BaseUOM_Name")
     *
     * @var string
     */
    protected $basicUnitOfMeasurementName = Material::DEFAULT_BASE_UNIT_OF_MEASUREMENT_NAME;

    /**
     * Группа материалов
     * Содержит код группы материалов, 9-значный код.
     * Определяет нахождение товара в SAP в товарной иерархии.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Matkl")
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $sapMaterialGroupId = 0;

    /**
     * Количество в упаковке
     * Содержит количество единиц товара в одной упаковке,
     * за покупку которого пользователю может быть доступна скидка по условиям предоставления сервиса «Округлить до упаковки».
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Multi_Factor")
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $countInPack = 0;

    /**
     * Выгружать в ИМ
     * На данный момент не используется
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("For_IM")
     * @Serializer\Type("sap_bool")
     *
     * @internal
     * @var bool
     */
    protected $uploadToIm = false;

    /**
     * Не выгружать в ИМ
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("No_Upload_IM")
     * @Serializer\Type("sap_bool")
     *
     * @var bool
     */
    protected $notUploadToIm = false;

    /**
     * Недоступно для курьерской доставки
     * Содержит признак недоступности для курьерской доставки.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("No_Sale")
     * @Serializer\Type("sap_bool")
     * @var bool
     */
    protected $noCourierDelivery = false;

    /**
     * Группа материалов
     * Содержит код группы материалов, 9-значный код.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Group")
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $materialGroupId = 0;

    /**
     * Название группы материалов
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Group_Name")
     * @Serializer\Type("string")
     *
     * @internal
     * @var string
     */
    protected $materialGroupName = '';

    /**
     * Код бренда
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Brand")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $brandCode = '';

    /**
     * Содержит название бренда товара.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Brand_Name")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $brandName = '';

    /**
     * Код страны-производителя
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Country")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $countryOfOriginCode = '';

    /**
     * Содержит название страны-производителя товара.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Country_Name")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $countryOfOriginName = '';

    /**
     * Содержит розничную цену торгового предложения на момент выгрузки товара.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Price_Retail")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $retailPrice = 0;

    /**
     * Единицы измерения
     *
     * @Serializer\XmlList(inline=true, entry="UOM")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Offers\UnitOfMeasurement>")
     *
     * @var Collection|UnitOfMeasurement[]
     */
    protected $unitsOfMeasure;

    /**
     * Свойства товара
     *
     * @Serializer\SerializedName("Properties")
     * @Serializer\XmlElement()
     * @Serializer\Type("FourPaws\SapBundle\Dto\In\Offers\Properties")
     *
     * @var Properties
     */
    protected $properties;

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
     * @return Material
     */
    public function setOfferXmlId(string $offerXmlId): Material
    {
        $this->offerXmlId = $offerXmlId;
        return $this;
    }

    /**
     * @return string
     */
    public function getOldOfferXmlId(): string
    {
        return $this->oldOfferXmlId;
    }

    /**
     * @param string $oldOfferXmlId
     *
     * @return Material
     */
    public function setOldOfferXmlId(string $oldOfferXmlId): Material
    {
        $this->oldOfferXmlId = $oldOfferXmlId;
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
     *
     * @return Material
     */
    public function setOfferName(string $offerName): Material
    {
        $this->offerName = $offerName;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductName(): string
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     *
     * @return Material
     */
    public function setProductName(string $productName): Material
    {
        $this->productName = $productName;
        return $this;
    }

    /**
     * @return string
     */
    public function getBasicUnitOfMeasurementCode(): string
    {
        return $this->basicUnitOfMeasurementCode;
    }

    /**
     * @param string $basicUnitOfMeasurementCode
     *
     * @return Material
     */
    public function setBasicUnitOfMeasurementCode(string $basicUnitOfMeasurementCode): Material
    {
        $this->basicUnitOfMeasurementCode = $basicUnitOfMeasurementCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getBasicUnitOfMeasurementName(): string
    {
        return $this->basicUnitOfMeasurementName;
    }

    /**
     * @param string $basicUnitOfMeasurementName
     *
     * @return Material
     */
    public function setBasicUnitOfMeasurementName(string $basicUnitOfMeasurementName): Material
    {
        $this->basicUnitOfMeasurementName = $basicUnitOfMeasurementName;
        return $this;
    }

    /**
     * @return int
     */
    public function getSapMaterialGroupId(): int
    {
        return $this->sapMaterialGroupId;
    }

    /**
     * @param int $sapMaterialGroupId
     *
     * @return Material
     */
    public function setSapMaterialGroupId(int $sapMaterialGroupId): Material
    {
        $this->sapMaterialGroupId = $sapMaterialGroupId;
        return $this;
    }

    /**
     * @return int
     */
    public function getCountInPack(): int
    {
        return $this->countInPack;
    }

    /**
     * @param int $countInPack
     *
     * @return Material
     */
    public function setCountInPack(int $countInPack): Material
    {
        $this->countInPack = $countInPack;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUploadToIm(): bool
    {
        return $this->uploadToIm;
    }

    /**
     * @param bool $uploadToIm
     *
     * @return Material
     */
    public function setUploadToIm(bool $uploadToIm): Material
    {
        $this->uploadToIm = $uploadToIm;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNotUploadToIm(): bool
    {
        return $this->notUploadToIm;
    }

    /**
     * @param bool $notUploadToIm
     *
     * @return Material
     */
    public function setNotUploadToIm(bool $notUploadToIm): Material
    {
        $this->notUploadToIm = $notUploadToIm;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNoCourierDelivery(): bool
    {
        return $this->noCourierDelivery;
    }

    /**
     * @param bool $noCourierDelivery
     *
     * @return Material
     */
    public function setNoCourierDelivery(bool $noCourierDelivery): Material
    {
        $this->noCourierDelivery = $noCourierDelivery;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaterialGroupId(): int
    {
        return $this->materialGroupId;
    }

    /**
     * @param int $materialGroupId
     *
     * @return Material
     */
    public function setMaterialGroupId(int $materialGroupId): Material
    {
        $this->materialGroupId = $materialGroupId;
        return $this;
    }

    /**
     * @return string
     */
    public function getMaterialGroupName(): string
    {
        return $this->materialGroupName;
    }

    /**
     * @param string $materialGroupName
     *
     * @return Material
     */
    public function setMaterialGroupName(string $materialGroupName): Material
    {
        $this->materialGroupName = $materialGroupName;
        return $this;
    }

    /**
     * @return string
     */
    public function getBrandCode(): string
    {
        return $this->brandCode;
    }

    /**
     * @param string $brandCode
     *
     * @return Material
     */
    public function setBrandCode(string $brandCode): Material
    {
        $this->brandCode = $brandCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getBrandName(): string
    {
        return $this->brandName;
    }

    /**
     * @param string $brandName
     *
     * @return Material
     */
    public function setBrandName(string $brandName): Material
    {
        $this->brandName = $brandName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryOfOriginCode(): string
    {
        return $this->countryOfOriginCode;
    }

    /**
     * @param string $countryOfOriginCode
     *
     * @return Material
     */
    public function setCountryOfOriginCode(string $countryOfOriginCode): Material
    {
        $this->countryOfOriginCode = $countryOfOriginCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryOfOriginName(): string
    {
        return $this->countryOfOriginName;
    }

    /**
     * @param string $countryOfOriginName
     *
     * @return Material
     */
    public function setCountryOfOriginName(string $countryOfOriginName): Material
    {
        $this->countryOfOriginName = $countryOfOriginName;
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
     * @return Material
     */
    public function setRetailPrice(float $retailPrice): Material
    {
        $this->retailPrice = $retailPrice;
        return $this;
    }

    /**
     * @return Collection|UnitOfMeasurement[]
     */
    public function getUnitsOfMeasure()
    {
        return $this->unitsOfMeasure;
    }

    /**
     * @param Collection|UnitOfMeasurement[] $unitsOfMeasure
     *
     * @return Material
     */
    public function setUnitsOfMeasure($unitsOfMeasure)
    {
        $this->unitsOfMeasure = $unitsOfMeasure;
        return $this;
    }

    /**
     * @return Properties
     */
    public function getProperties(): Properties
    {
        return $this->properties;
    }

    /**
     * @param Properties $properties
     *
     * @return Material
     */
    public function setProperties(Properties $properties): Material
    {
        $this->properties = $properties;
        return $this;
    }
}
