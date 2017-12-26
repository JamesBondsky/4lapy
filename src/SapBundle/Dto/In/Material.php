<?php

namespace FourPaws\SapBundle\Dto\In;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Material
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
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Matnr")
     *
     * @var int
     */
    protected $offerXmlId = 0;

    /**
     * Старый номер материала
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Old_Number")
     *
     * @var int
     */
    protected $oldOfferXmlId = 0;

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
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\UnitOfMeasurement>")
     *
     * @var Collection|UnitOfMeasurement[]
     */
    protected $unitsOfMeasure;

    /**
     * Свойства товара
     *
     * @Serializer\SerializedName("Properties")
     * @Serializer\XmlElement()
     * @Serializer\Type("FourPaws\SapBundle\Dto\In\Properties")
     *
     * @var Properties
     */
    protected $properties;
}
