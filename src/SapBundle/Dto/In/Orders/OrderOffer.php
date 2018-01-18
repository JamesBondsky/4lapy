<?php

namespace FourPaws\SapBundle\Dto\In\Orders;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Product
 * @package FourPaws\SapBundle\Dto\Out\Orders
 * @Serializer\XmlRoot(name="Item")
 */
class OrderOffer
{
    /**
     * Содержит номер позиции торгового предложения в заказе
     * 6-значный цифровой код, формат: 000010
     *
     * В нашем случае приводится к числу
     * Минимально - 1
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Pos_NR")
     * @Serializer\Type("sap_position")
     *
     * @var int
     */
    protected $position;

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
     * @Serializer\SerializedName("Qty_UOM")
     *
     * @var string
     */
    protected $unitOfMeasureCode = '';

    /**
     * Цена за единицу товара в заказе
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Price")
     *
     * @var float
     */
    protected $unitPrice = 0;
}
