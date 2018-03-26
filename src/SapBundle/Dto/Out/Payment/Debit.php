<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\ConfirmPayment;

/**
 * Class Debit
 *
 * @package FourPaws\SapBundle\Dto\Out\Payment
 */
class Debit
{
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
     * Номер заказа SAP
     *
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Order_SAP")
     * @Serializer\XmlAttribute()
     *
     * @var int
     */
    protected $sapOrderId = 0;

    /**
     * Номер заказа Сайт
     *
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Order_BITRIX")
     * @Serializer\XmlAttribute()
     *
     * @var int
     */
    protected $bitrixOrderId = 0;


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
     * Если выбран способ доставки самовывозом, содержит код магазина или код пункта выдачи заказов DPD.
     * Если выбран способ доставки курьером, содержит адрес доставки, который был указан при оформлении заказа.
     * Адрес должен быть сформирован в соответствии с используемым справочником адресов.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("ClientAdress")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $clientAddress = '';

    /**
     * Содержит буквенно-цифровой код транзакции оплаты при оплате на Сайте.
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
     * Статус оплаты
     *
     * 01 – ZIMN Оплачено;
     * 02 – ZIMN Не оплачено;
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("OrderPayStat")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $payStatus = '';

    /**
     * Дата оплаты
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("PaymentDate")
     * @Serializer\Type("DateTime<'Ymd'>")
     *
     * @var \DateTime
     */
    protected $paymentDate;

    /**
     * Содержит номер контрольно-кассовой машины (кассы), на которой был сформирован онлайн чек по предоплаченному
     * заказу.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("KKM_FR")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $kkm = '';


    /**
     * Содержит номер Z отчета кассы, на которой был сформирован онлайн чек по предоплаченному заказу.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("KKM_LIST_ID")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $zReport = '';

    /**
     * @return \DateTime
     */
    public function getDeliveryDate(): \DateTime
    {
        return $this->deliveryDate;
    }

    /**
     * @param \DateTime $deliveryDate
     * @return Debit
     */
    public function setDeliveryDate(\DateTime $deliveryDate): Debit
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    /**
     * @return int
     */
    public function getSapOrderId(): int
    {
        return $this->sapOrderId;
    }

    /**
     * @param int $sapOrderId
     * @return Debit
     */
    public function setSapOrderId(int $sapOrderId): Debit
    {
        $this->sapOrderId = $sapOrderId;

        return $this;
    }

    /**
     * @return int
     */
    public function getBitrixOrderId(): int
    {
        return $this->bitrixOrderId;
    }

    /**
     * @param int $bitrixOrderId
     * @return Debit
     */
    public function setBitrixOrderId(int $bitrixOrderId): Debit
    {
        $this->bitrixOrderId = $bitrixOrderId;

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
     * @return Debit
     */
    public function setPayMerchantCode(string $payMerchantCode): Debit
    {
        $this->payMerchantCode = $payMerchantCode;

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
     * @return Debit
     */
    public function setClientId(int $clientId): Debit
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
     * @return Debit
     */
    public function setClientFio(string $clientFio): Debit
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
     * @return Debit
     */
    public function setClientPhone(string $clientPhone): Debit
    {
        $this->clientPhone = $clientPhone;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientAddress(): string
    {
        return $this->clientAddress;
    }

    /**
     * @param string $clientAddress
     * @return Debit
     */
    public function setClientAddress(string $clientAddress): Debit
    {
        $this->clientAddress = $clientAddress;

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
     * @return Debit
     */
    public function setPayHoldTransaction(string $payHoldTransaction): Debit
    {
        $this->payHoldTransaction = $payHoldTransaction;

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
     * @return Debit
     */
    public function setPayStatus(string $payStatus): Debit
    {
        $this->payStatus = $payStatus;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPaymentDate(): \DateTime
    {
        return $this->paymentDate;
    }

    /**
     * @param \DateTime $paymentDate
     * @return Debit
     */
    public function setPaymentDate(\DateTime $paymentDate): Debit
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getKkm(): string
    {
        return $this->kkm;
    }

    /**
     * @param string $kkm
     * @return Debit
     */
    public function setKkm(string $kkm): Debit
    {
        $this->kkm = $kkm;

        return $this;
    }

    /**
     * @return string
     */
    public function getZReport(): string
    {
        return $this->zReport;
    }

    /**
     * @param string $zReport
     * @return Debit
     */
    public function setZReport(string $zReport): Debit
    {
        $this->zReport = $zReport;

        return $this;
    }
}
