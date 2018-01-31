<?php

namespace FourPaws\External\Manzana\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class Cheque
 *
 * @package FourPaws\External\Manzana\Model
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("Cheque")
 */
class Cheque
{
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("ChequeId")
     */
    public $chequeId;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("ChequeNumber")
     */
    public $chequeNumber;
    
    /**
     * @Type("manzana_date_time_short")
     * @SerializedName("Date")
     */
    public $date;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("ChequeName")
     */
    public $chequeName;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("Discount")
     */
    public $discount;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("Summ")
     */
    public $sum;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("SummDiscounted")
     */
    public $sumDiscounted;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("BusinessUnit")
     */
    public $businessUnit;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("OrganizationId")
     */
    public $organizationId;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("OrganizationName")
     */
    public $organizationName;
    
    /**
     * @Type("manzana_date_time_short")
     * @SerializedName("Processed")
     */
    public $processed;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("HasItems")
     * 1 - No, 2 -Yes
     */
    public $hasItems;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("OperationTypeCode")
     * 1 - Purchase(Продажа) 2 - Return(Возврат)
     */
    public $operationTypeCode;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("OperationTypeText")
     * 1 - Purchase(Продажа) 2 - Return(Возврат)
     */
    public $operationTypeText;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("Bonus")
     */
    public $bonus;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("PaidByBonus")
     */
    public $paidByBonus;
    
    /**
     * @Type("ArrayCollection<FourPaws\External\Manzana\Model\Payment>")
     * @XmlList(entry="Payment", inline=true)
     * @SerializedName("Payments")
     */
    public $payments;
    
    /**
     * @return mixed
     */
    public function hasItemsBool()
    {
        return (int)$this->hasItems === 2;
    }
}