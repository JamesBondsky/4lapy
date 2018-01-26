<?php

namespace FourPaws\SapBundle\Dto\In\Orders;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Order
 * @package FourPaws\SapBundle\Dto\Out\Orders
 * @Serializer\XmlRoot(name="ns0:mt_OrdersIM")
 * @Serializer\XmlNamespace(uri="urn:4lapy.ru:BITRIX_2_ERP:DataExchange", prefix="ns0")
 */
class Order
{
    /**
     * Содержит номер заказа в SAP.
     * Поле обязательно для заполнения
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Doc_NR")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $externalId = '';

    /**
     * Содержит номер заказа в Системе.
     * Поле обязательно для заполнения
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Doc_NR_Bitrix")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $id = '';

    /**
     * Дата заказа
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("OrderDate")
     * @Serializer\Type("DateTime<'Ymd'>")
     *
     * @var \DateTime
     */
    //protected $dateInsert;

    /**
     * ID покупателя
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Customer_ID")
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $clientId = 0;

    /**
     * Содержит фамилию, имя и отчество покупателя.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Customer_FIO")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $clientFio = '';

    /**
     * Номер телефона покупателя
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Phone_1")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $clientPhone = '';

    /**
     * Номер телефона для связи по заказу.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Phone_2")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $clientOrderPhone = '';

    /**
     * Дата доставки
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("RequestDeliveryDate")
     * @Serializer\Type("DateTime<'Ymd'>")
     *
     * @var \DateTime
     */
    protected $deliveryDate;

    /**
     * Интервал доставки
     * 1    (09:00 – 18:00);
     * 2    (18:00 – 24:00);
     * 3    (08:00 – 12:00);
     * 4    (12:00 – 16:00);
     * 5    (16:00 – 20:00);
     * 6    (20:00 – 24:00).
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("AgreedDeliveryDate")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $deliveryTimeInterval = '';
    
    /**
     * Содержит статус заказа.
     *
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Stat")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $status;

    /**
     * Способ оплаты
     * 05 – Онлайн-оплата;
     * Пусто – для других способов оплаты.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("PayType")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $payType = '';

    /**
     * Статус оплаты
     *
     * 01 – ZIMN Оплачено;
     * 02 – ZIMN Не оплачено;
     * 03 – ZIMN Предоплачено.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("PayStat")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $payStatus = '';

    /**
     * Номер бонусной карты покупателя, 13-значный цифровой код.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Discount_Card")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $bonusCard = '';

    /**
     * Сумма оплаты баллами
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Points_Sum")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $bonusPayedCount = 0;

    /**
     * Содержит сумму заказа. Число десятичных знаков после запятой – 2.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Total_Sum")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $totalSum = 0;

    /**
     * Содержит номер файла выгрузки
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Plant")
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $dn;

    /**
     * Содержит код склада или магазина.
     * Значение параметра может отличаться от переданного при оформлении заказа ClientAdress.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("DN")
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $stock;

    /**
     * Содержит данные о сбытовой организации SAP, значение по умолчанию «1001».
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("SalesOrg")
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $salesOrganization = 1001;

    /**
     * Содержит данные о канале сбыта SAP, значение по умолчанию «05» – интернет-магазин.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("DistrChannel")
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $distributionChannel = 05;

    /**
     * Содержит данные об отделе сбыта SAP, значение по умолчанию «01» – интернет-магазин.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Divizion")
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $division = 01;

    /**
     * Товары в заказе
     *
     * @Serializer\XmlList(inline=true, entry="Item")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Orders\OrderOffer>")
     *
     * @var Collection|OrderOffer[]
     */
    protected $products;

    /**
     * Адрес доставки для:
     * 01 – Курьерская доставка из РЦ;
     * 06 – Курьерская доставка из магазина;
     * 07 – Курьерская доставка внешним подрядчиком;
     * 08 – РЦ – магазин - домой
     *
     * @Serializer\SkipWhenEmpty()
     * @Serializer\Type("FourPaws\SapBundle\Dto\In\Orders\DeliveryAddress")
     * @Serializer\SerializedName("DeliveryAddress")
     *
     * @var null|DeliveryAddress
     */
    protected $deliveryAddress;
}
