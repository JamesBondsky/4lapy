<?php

namespace FourPaws\SapBundle\Dto\Out\Orders;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Order
 * @package FourPaws\SapBundle\Dto\Out\Orders
 * @Serializer\XmlRoot(name="ns0:mt_OrdersIM")
 * @Serializer\XmlNamespace(uri="urn:4lapy.ru:BITRIX_2_ERP:DataExchange", prefix="ns0")
 */
class Order
{
    const DEFAULT_CONTRACTOR_CODE = '0000802070';

    /**
     * Содержит номер заказа в Системе.
     * Если заказ создан в мобильном приложении (атрибут BSARK имеет значение «MOBI»),
     * номер заказа должен содержать букву «m» перед номером.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("OrderNumber")
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
    protected $dateInsert;

    /**
     * ID покупателя
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("ClientID")
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $clientId = 0;

    /**
     * Содержит фамилию, имя и отчество покупателя.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("ClientName")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $clientFio = '';

    /**
     * Номер телефона покупателя, который был указан при оформлении заказа.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("ClientPhone")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $clientPhone = '';

    /**
     * Номер телефона для связи по заказу.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("ClientPhone_2")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $clientOrderPhone = '';

    /**
     * Адресс клиента
     * Для способов получения заказа 02, 04 содержит код магазина или пункта выдачи заказов DPD
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("ClientAdress")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $deliveryAddressOrPoint = '';

    /**
     * Комментарий покупателя, заполненный при оформлении заказа.
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("ClientComment")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $clientComment = '';

    /**
     * Место создания заказа
     * DFUE – заказ создан на Сайте;
     * MOBI – заказ создан в мобильном приложении;
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("BSARK")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $orderSource = '';

    /**
     * Способ коммуникации с покупателем для подтверждения заказа
     * 01 – SMS;
     * 02 – Телефонный звонок;
     * 03 – Телефонный звонок (анализ);
     *
     * Если при способе получения заказа 02 или 08 в выбранном магазине товары на сумму более 90%
     * от суммы заказа есть в наличии, устанавливается значение 03 – Телефонный звонок (анализ).
     * В остальных случаях указывается значение, которое клиент указал на сайте.
     *
     * Для способа получения заказа 07 устанавливается значение 02 – Телефонный звонок.
     * Для способа получения заказа 04 или 06 устанавливается значение 01 – СМС
     *
     * Для способов получения заказа 01, 02, 08 устанавливается значение, выбранное пользователем при оформлении заказа.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Communic")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $communicationType = '';

    /**
     * Способ получения заказа
     *
     * 01 – Курьерская доставка из РЦ;
     * 02 – Самовывоз из магазина;
     * 03 – Самовывоз из магазина (значение не передается Сайтом);
     * 04 – Отложить в магазине;
     * 06 – Курьерская доставка из магазина;
     * 07 – Доставка внешним подрядчиком (курьер или самовывоз из пункта выдачи заказов);
     * 08 – РЦ – магазин – домой.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("DeliveryType")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $deliveryType = '';

    /**
     * Тип доставки подрядчиком
     * Поле должно быть заполнено, если выбран способ получения заказа 07.
     * ТД – от терминала до двери покупателя;
     * ТТ – от терминала до пункта выдачи заказов.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("SPserviceVariant")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $contractorDeliveryType = '';

    /**
     * Код подрядчика
     * Содержит код подрядчика в SAP
     * Поле должно быть заполнено, если выбран способ получения заказа 07.
     * Значение по умолчанию 0000802070
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("CARRIER")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $contractorCode = Order::DEFAULT_CONTRACTOR_CODE;

    /**
     * Дата доставки
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("DeliveryDate")
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
     * @Serializer\SerializedName("OrderTime")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $deliveryTimeInterval = '';
}
