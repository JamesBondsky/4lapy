<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\External\Manzana\Model;

use FourPaws\Helpers\DateHelper;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class CardByContractCards
 *
 * @package FourPaws\External\Manzana\Model
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("Card")
 */
class CardByContractCards
{
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_bonuscardid")
     */
    public $cardId;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_number")
     */
    public $cardNumber;

    /**
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("pl_bonustype")
     */
    public $bonusType;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_bonustype_text")
     */
    public $bonusTypeText;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_cardtypename")
     */
    public $cardTypeName;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("pl_balance")
     */
    public $balance;
    
    /**
     * Скидка
     *
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("pl_discount")
     */
    public $discount;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("pl_active_balance")
     */
    public $activeBalance;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("pl_summ")
     */
    public $sum;
    
    /**
     * Сумма со скидкой
     *
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("pl_summdiscounted")
     */
    public $sumDiscounted;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("pl_status")
     */
    public $status;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_status_text")
     */
    public $statusText;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_external_id")
     */
    public $externalId;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("pl_collaboration_type")
     */
    public $collaborationType;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_collaboration_type_text")
     */
    public $collaborationTypeText;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_masteraccountid")
     */
    public $masterAccountId;
    
    /**
     * @var \DateTimeImmutable
     * @XmlElement(cdata=false)
     * @Type("manzana_date_time_short")
     * @SerializedName("pl_expirydate")
     */
    public $expireDate;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_emission_taskidname")
     */
    public $emissionTaskIdName;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_emission_taskid")
     */
    public $emissionTaskId;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("manzana_date_time_short")
     * @SerializedName("pl_effectdate")
     */
    public $effectDate;
    
    /**
     * Получено баллов
     *
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("pl_credit")
     */
    public $credit;
    
    /**
     * Потрачено баллов
     *
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("pl_debet")
     */
    public $debit;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("balanceactionlimit")
     */
    public $balanceActionLimit;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("balanceactionnolimit")
     */
    public $balanceActionNoLimit;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("balanceextralimit")
     */
    public $balanceExtraLimit;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("balanceextranolimit")
     */
    public $balanceExtraNoLimit;
    
    /**
     * @param string $format
     *
     * @return string
     */
    public function getFormatExpireDate(string $format = 'd #m# Y') : string
    {
        return DateHelper::replaceRuMonth($this->getExpireDate()->format($format), DateHelper::GENITIVE);
    }
    
    /**
     * @return \DateTimeImmutable
     */
    public function getExpireDate() : \DateTimeImmutable
    {
        return $this->expireDate;
    }
    
    /**
     * @return bool
     */
    public function isActive() : bool
    {
        return $this->status === 2;
    }
}
