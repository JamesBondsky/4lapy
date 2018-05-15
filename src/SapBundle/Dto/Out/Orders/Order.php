<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\Out\Orders;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Order
 *
 * @package FourPaws\SapBundle\Dto\Out\Orders
 *
 * @Serializer\XmlRoot(name="ns0:mt_OrdersIM")
 * @Serializer\XmlNamespace(uri="urn:4lapy.ru:BITRIX_2_ERP:DataExchange", prefix="ns0")
 */
class Order
{
    public const DEFAULT_CONTRACTOR_CODE = '0000802070';

    public const ORDER_SOURCE_MOBILE_APP = 'MOBI';
    public const ORDER_SOURCE_SITE = 'DFUE';

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
     * Адрес клиента
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

    /**
     * Способ оплаты
     * 05 – Онлайн-оплата;
     * Пусто – для других способов оплаты.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("OrderPayType")
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
     * @Serializer\SerializedName("OrderPayStat")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $payStatus = '';

    /**
     * Транзакция холдирования денежных средств
     * Поле должно быть заполнено, если выбран способ оплаты 05
     *
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Tranzaction")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $payHoldTransaction = '';

    /**
     * Дата холдирования денежных средств
     * Поле должно быть заполнено, если выбран способ оплаты 05
     *
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("HoldDate")
     * @Serializer\Type("DateTime<'Ymd'>")
     * @Serializer\SkipWhenEmpty()
     *
     * @var null|\DateTime
     */
    protected $payHoldDate;

    /**
     * Номер мерчанта платежной системы
     * Поле должно быть заполнено, если выбран способ оплаты 05.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("Merchant")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $payMerchantCode = '';

    /**
     * Предоплата
     * Поле должно быть заполнено, если выбран способ оплаты 05.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("OrderPaySum")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $prePayedSum = 0;

    /**
     * Номер бонусной карты покупателя, 13-значный цифровой код.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("BonusCard")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $bonusCard = '';

    /**
     * Сумма оплаты баллами
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("SUM_POINTS")
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $bonusPayedCount = 0;

    /**
     * Товары в заказе
     *
     * @Serializer\XmlList(inline=true, entry="Item")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\Out\Orders\OrderOffer>")
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
     * @Serializer\Type("FourPaws\SapBundle\Dto\Out\Orders\DeliveryAddress")
     * @Serializer\SerializedName("DeliveryAddress")
     *
     * @var null|DeliveryAddress
     */
    protected $deliveryAddress;
    
    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
    
    /**
     * @param string $id
     *
     * @return Order
     */
    public function setId(string $id): Order
    {
        $this->id = $id;

        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getDateInsert(): \DateTime
    {
        return $this->dateInsert;
    }
    
    /**
     * @param \DateTime $dateInsert
     *
     * @return Order
     */
    public function setDateInsert(\DateTime $dateInsert): Order
    {
        $this->dateInsert = $dateInsert;

        return $this;
    }
    
    /**
     * @return int
     */
    public function getClientId(): int
    {
        return $this->clientId;
    }
    
    /**
     * @param int $clientId
     *
     * @return Order
     */
    public function setClientId(int $clientId): Order
    {
        $this->clientId = $clientId;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getClientFio(): string
    {
        return $this->clientFio;
    }
    
    /**
     * @param string $clientFio
     *
     * @return Order
     */
    public function setClientFio(string $clientFio): Order
    {
        $this->clientFio = $clientFio;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getClientPhone(): string
    {
        return $this->clientPhone;
    }
    
    /**
     * @param string $clientPhone
     *
     * @return Order
     */
    public function setClientPhone(string $clientPhone): Order
    {
        $this->clientPhone = $clientPhone;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getClientOrderPhone(): string
    {
        return $this->clientOrderPhone;
    }
    
    /**
     * @param string $clientOrderPhone
     *
     * @return Order
     */
    public function setClientOrderPhone(string $clientOrderPhone): Order
    {
        $this->clientOrderPhone = $clientOrderPhone;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getDeliveryAddressOrPoint(): string
    {
        return $this->deliveryAddressOrPoint;
    }
    
    /**
     * @param string $deliveryAddressOrPoint
     *
     * @return Order
     */
    public function setDeliveryAddressOrPoint(string $deliveryAddressOrPoint): Order
    {
        $this->deliveryAddressOrPoint = $deliveryAddressOrPoint;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getClientComment(): string
    {
        return $this->clientComment;
    }
    
    /**
     * @param string $clientComment
     *
     * @return Order
     */
    public function setClientComment(string $clientComment = ''): Order
    {
        $this->clientComment = $clientComment ?? '';

        return $this;
    }
    
    /**
     * @return string
     */
    public function getOrderSource(): string
    {
        return $this->orderSource;
    }
    
    /**
     * @param string $orderSource
     *
     * @return Order
     */
    public function setOrderSource(string $orderSource): Order
    {
        $this->orderSource = $orderSource;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getCommunicationType(): string
    {
        return $this->communicationType;
    }
    
    /**
     * @param string $communicationType
     *
     * @return Order
     */
    public function setCommunicationType(string $communicationType): Order
    {
        $this->communicationType = $communicationType;

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
     *
     * @return Order
     */
    public function setDeliveryType(string $deliveryType): Order
    {
        $this->deliveryType = $deliveryType;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getContractorDeliveryType(): string
    {
        return $this->contractorDeliveryType;
    }
    
    /**
     * @param string $contractorDeliveryType
     *
     * @return Order
     */
    public function setContractorDeliveryType(string $contractorDeliveryType): Order
    {
        $this->contractorDeliveryType = $contractorDeliveryType;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getContractorCode(): string
    {
        return $this->contractorCode;
    }
    
    /**
     * @param string $contractorCode
     *
     * @return Order
     */
    public function setContractorCode(string $contractorCode): Order
    {
        $this->contractorCode = $contractorCode;

        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getDeliveryDate(): \DateTime
    {
        return $this->deliveryDate;
    }
    
    /**
     * @param \DateTime $deliveryDate
     *
     * @return Order
     */
    public function setDeliveryDate(\DateTime $deliveryDate): Order
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getDeliveryTimeInterval(): string
    {
        return $this->deliveryTimeInterval;
    }
    
    /**
     * @param string $deliveryTimeInterval
     *
     * @return Order
     */
    public function setDeliveryTimeInterval(string $deliveryTimeInterval): Order
    {
        $this->deliveryTimeInterval = $deliveryTimeInterval;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getPayType(): string
    {
        return $this->payType;
    }
    
    /**
     * @param string $payType
     *
     * @return Order
     */
    public function setPayType(string $payType): Order
    {
        $this->payType = $payType;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getPayStatus(): string
    {
        return $this->payStatus;
    }
    
    /**
     * @param string $payStatus
     *
     * @return Order
     */
    public function setPayStatus(string $payStatus): Order
    {
        $this->payStatus = $payStatus;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getPayHoldTransaction(): string
    {
        return $this->payHoldTransaction;
    }
    
    /**
     * @param string $payHoldTransaction
     *
     * @return Order
     */
    public function setPayHoldTransaction(string $payHoldTransaction): Order
    {
        $this->payHoldTransaction = $payHoldTransaction;

        return $this;
    }
    
    /**
     * @return null|\DateTime
     */
    public function getPayHoldDate(): \DateTime
    {
        return $this->payHoldDate;
    }
    
    /**
     * @param null|\DateTime $payHoldDate
     *
     * @return Order
     */
    public function setPayHoldDate(\DateTime $payHoldDate): Order
    {
        $this->payHoldDate = $payHoldDate;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getPayMerchantCode(): string
    {
        return $this->payMerchantCode;
    }
    
    /**
     * @param string $payMerchantCode
     *
     * @return Order
     */
    public function setPayMerchantCode(string $payMerchantCode): Order
    {
        $this->payMerchantCode = $payMerchantCode;

        return $this;
    }
    
    /**
     * @return float
     */
    public function getPrePayedSum(): float
    {
        return $this->prePayedSum;
    }
    
    /**
     * @param float $prePayedSum
     *
     * @return Order
     */
    public function setPrePayedSum(float $prePayedSum): Order
    {
        $this->prePayedSum = $prePayedSum;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getBonusCard(): string
    {
        return $this->bonusCard;
    }
    
    /**
     * @param string $bonusCard
     *
     * @return Order
     */
    public function setBonusCard(string $bonusCard): Order
    {
        $this->bonusCard = $bonusCard;

        return $this;
    }
    
    /**
     * @return float
     */
    public function getBonusPayedCount(): float
    {
        return $this->bonusPayedCount;
    }
    
    /**
     * @param float $bonusPayedCount
     *
     * @return Order
     */
    public function setBonusPayedCount(float $bonusPayedCount): Order
    {
        $this->bonusPayedCount = $bonusPayedCount;

        return $this;
    }
    
    /**
     * @return Collection|OrderOffer[]
     */
    public function getProducts()
    {
        return $this->products;
    }
    
    /**
     * @param Collection|OrderOffer[] $products
     *
     * @return Order
     */
    public function setProducts($products): Order
    {
        $this->products = $products;

        return $this;
    }
    
    /**
     * @return null|DeliveryAddress
     */
    public function getDeliveryAddress(): DeliveryAddress
    {
        return $this->deliveryAddress;
    }
    
    /**
     * @param null|DeliveryAddress $deliveryAddress
     *
     * @return Order
     */
    public function setDeliveryAddress(DeliveryAddress $deliveryAddress): Order
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }
}
