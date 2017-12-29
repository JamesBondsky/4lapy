<?php

namespace FourPaws\SapBundle\Dto\Out\Orders;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Product
 * @package FourPaws\SapBundle\Dto\Out\Orders
 * @Serializer\XmlRoot(name="Item")
 */
class OrderOffer
{
    const DEFAULT_PROVIDER_POINT = 'DC01';

    /**
     * Содержит номер позиции торгового предложения в заказе
     * 6-значный цифровой код, формат: 000010
     *
     * В нашем случае приводится к числу
     * Минимально - 1
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("PosNumber")
     * @Serializer\Type("sap_position")
     *
     * @var int
     */
    protected $position = 1;

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
     * Количество
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Qty")
     *
     * @var int
     */
    protected $quantity = 0;

    /**
     * Код единицы измерения торгового предложения
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UOM")
     *
     * @var string
     */
    protected $unitOfMeasureCode = '';

    /**
     * Код склада/магазина откуда будет забран заказ
     * Или откуда повезут непосредственно заказ
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Plant")
     *
     * @var string
     */
    protected $deliveryFromPoint = '';

    /**
     * Код склада/поставщика откуда будет произведена отгрузка по заказу
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UnloadingPoint")
     *
     * @var string
     */
    protected $deliveryShipmentPoint = '';

    /**
     * Цена за единицу товара в заказе
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Amount")
     *
     * @var float
     */
    protected $unitPrice = 0;

    /**
     * Начислять ли бонусы по позиции товара
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("signcharge")
     *
     * @var bool
     */
    protected $chargeBonus = false;
}
