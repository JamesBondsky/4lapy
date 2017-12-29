<?php

namespace FourPaws\SapBundle\Dto\In\Prices;

use JMS\Serializer\Annotation as Serializer;

class Item
{
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
     * Содержит розничную цену торгового предложения на момент выгрузки товара.
     * Система должна сбросить региональную цену и установить в указанном регионе глобальную цену.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Price_Retail")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $retailPrice = 0;

    /**
     * Цена по акции
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Price_Action")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $actionPrice = 0;

    /**
     * Тип цены
     *
     * Содержит тип цены. Тип поля – единственный выбор из значений:
     * Пусто – розничная цена;
     * VKA0 – цена по акции «Рекламная цена»;
     * ZRBT – цена по акции «Скидка на товар»
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Cond_For_Action")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $priceType = '';


    /**
     * Размер скидки на товар
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Cond_Value")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $discountValue = 0;
}
