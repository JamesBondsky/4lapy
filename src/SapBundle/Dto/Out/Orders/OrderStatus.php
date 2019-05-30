<?php

namespace FourPaws\SapBundle\Dto\Out\Orders;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class OrderStatus
 *
 * @package FourPaws\SapBundle\Dto\Out\Orders
 *
 * @Serializer\XmlRoot(name="ns0:mt_OrdersStatusesIM")
 * @Serializer\XmlNamespace(uri="urn:4lapy.ru:BITRIX_2_ERP:DataExchange", prefix="ns0")
 */
class OrderStatus
{
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
     * Способ получения заказа
     *
     * 01 – Курьерская доставка из РЦ;
     * 02 – Самовывоз из магазина;
     * 03 – Самовывоз из магазина (значение не передается Сайтом);
     * 04 – Отложить в магазине;
     * 06 – Курьерская доставка из магазина;
     * 07 – Доставка внешним подрядчиком (курьер или самовывоз из пункта выдачи заказов);
     * 08 – РЦ – магазин – домой.
     * 09 – Достависта
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("DeliveryType")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $deliveryType = '';

    /**
     * Статус заказа
     *
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("OrderStatus")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $status = '';

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return OrderStatus
     */
    public function setId(string $id): OrderStatus
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryType(): string
    {
        return $this->deliveryType;
    }

    /**
     * @param string $deliveryType
     * @return OrderStatus
     */
    public function setDeliveryType(string $deliveryType): OrderStatus
    {
        $this->deliveryType = $deliveryType;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return OrderStatus
     */
    public function setStatus(string $status): OrderStatus
    {
        $this->status = $status;
        return $this;
    }
}
