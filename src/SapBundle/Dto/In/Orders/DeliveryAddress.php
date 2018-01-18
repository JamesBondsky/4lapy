<?php

namespace FourPaws\SapBundle\Dto\In\Orders;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class DeliveryAddress
 *
 * @todo отнаследоваться от общего dto delivery
 *
 * @package FourPaws\SapBundle\Dto\In\Orders
 */
class DeliveryAddress
{
    /**
     * Страна
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("COUNTRY")
     *
     * @var string
     */
    protected $countryCode = 'RU';

    /**
     * Код региона
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("REGION")
     *
     * @var string
     */
    protected $regionCode = '';

    /**
     * Почтовый индекс
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("POSTCODE")
     *
     * @var string
     */
    protected $postCode = '';

    /**
     * Название города
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("CITY")
     *
     * @var string
     */
    protected $cityName = '';

    /**
     * Название улицы
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("STREET")
     *
     * @var string
     */
    protected $streetName = '';

    /**
     * Префикс улицы
     * ул, пр-т, б-р и т.д.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("STREETABBR")
     *
     * @var string
     */
    protected $streetPrefix = '';

    /**
     * Номер дома
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("HOUSE_NUM")
     *
     * @var string
     */
    protected $house = '';

    /**
     * Корпус дома
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("HOUSE_KORPUS")
     *
     * @var string
     */
    protected $housing = '';

    /**
     * Строение
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("HOUSE_STR")
     *
     * @var string
     */
    protected $building = '';

    /**
     * Владение
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("HOUSE_VLAD")
     *
     * @var string
     */
    protected $ownerShip = '';

    /**
     * Этаж
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("FLOOR")
     *
     * @var string
     */
    protected $floor = '';

    /**
     * Квартира/комната/номер офиса
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ROOMNUMBER")
     *
     * @var string
     */
    protected $roomNumber = '';

    /**
     * Номер пункта выдачи заказов подрядчика
     * Поле должно быть заполнено, если выбран способ получения заказа 07 и тип доставки внешним подрядчиком ТТ.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Serializer\SerializedName("SPTERMINALCODE")
     *
     * @var string
     */
    protected $deliveryPointCode = '';
}
