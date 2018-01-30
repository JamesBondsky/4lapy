<?php

namespace FourPaws\External\Manzana\Model;

use FourPaws\Helpers\DateHelper;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;
use FourPaws\External\Manzana\Model\ChequePayment;
use JMS\Serializer\Annotation\XmlList;

/**
 * Class ChequeByContractContactCheques
 * Элементы результата метода getChequesByContactId (contact_cheques)
 *
 * @package FourPaws\External\Manzana\Model
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("Cheque")
 */
class ChequeByContractContactCheques
{
    /**
     * ID чека
     *
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("ChequeId")
     */
    public $chequeId;

    /**
     * Номр чека
     *
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("ChequeNumber")
     */
    public $chequeNumber;

    /**
     * Дата
     *
     * @var \DateTimeImmutable
     * @XmlElement(cdata=false)
     * @Type("DateTimeImmutable<'Y-m-d\TH:i:s'>")
     * @SerializedName("Date")
     */
    public $date;

    /**
     * Название чека
     *
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("ChequeName")
     */
    public $chequeName;

    /**
     * Скидка
     *
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("Discount")
     */
    public $discount;

    /**
     * Сумма
     *
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("Summ")
     */
    public $sum;

    /**
     * Сумма со скидкой
     *
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("SummDiscounted")
     */
    public $sumDiscounted;

    /**
     * Название магазина
     *
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("BusinessUnit")
     */
    public $businessUnitName;

    /**
     * ID партнёра
     *
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("OrganizationId")
     */
    public $organizationId;

    /**
     * Название партнёра
     *
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("OrganizationName")
     */
    public $organizationName;

    /**
     * Дата обработки чека
     *
     * @XmlElement(cdata=false)
     * @Type("DateTimeImmutable<'Y-m-d\TH:i:s.u'>")
     * @SerializedName("Processed")
     */
    public $processed;

    /**
     * Идентификатор наличия позиций в чеке
     * 1-No (Нет) 2-Yes (Да)
     *
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("HasItems")
     */
    public $hasItems;

    /**
     * Код типа операции
     * 1-Purchase (Продажа) 2-Return (Возврат)
     *
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("OperationTypeCode")
     */
    public $operationTypeCode;

    /**
     * Название типа операции
     *
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("OperationTypeText")
     */
    public $operationTypeText;

    /**
     * Начисленный бонус
     *
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("Bonus")
     */
    public $bonus;

    /**
     * Оплачено бонусами
     *
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("PaidByBonus")
     */
    public $paidByBonus;

    /**
     * @Type("FourPaws\External\Manzana\Model\ChequePayments")
     * @SerializedName("Payments")
     */
    public $payments;

    public function getPaymentsArray() {
        return $this->payments->chequePayments ? $this->payments->chequePayments->toArray() : [];
    }
}
