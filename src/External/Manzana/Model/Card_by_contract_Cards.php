<?php

namespace FourPaws\External\Manzana\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class Card_by_contract_Cards
 *
 * @package FourPaws\External\Manzana\Model
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("Card")
 */
class Card_by_contract_Cards
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
     * @Type("DateTimeImmutable<'Y-m-d\TH:i:s'>")
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
     * @Type("DateTimeImmutable<'Y-m-d\TH:i:s.u'>")
     * @SerializedName("pl_effectdate")
     */
    public $effectDate;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("pl_credit")
     */
    public $credit;
    
    /**
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
     * @return \DateTimeImmutable
     */
    public function getExpireDate() : \DateTimeImmutable
    {
        return $this->expireDate;
    }
    
    /**
     * @param string $format
     *
     * @return string
     */
    public function getFormatExpireDate(string $format = 'd #m# Y') : string
    {
        return $this->replaceRuMonth($this->getExpireDate()->format($format));
    }
    
    /**
     * @param string $date
     *
     * @return string
     */
    public function replaceRuMonth(string $date) : string
    {
        /** @todo Русская локаль не помогла - может можно по другому? */
        $months = [
            '#1#'  => 'Января',
            '#2#'  => 'Февраля',
            '#3#'  => 'Марта',
            '#4#'  => 'Апреля',
            '#5#'  => 'Мая',
            '#6#'  => 'Июня',
            '#7#'  => 'Июля',
            '#8#'  => 'Августа',
            '#9#'  => 'Сентября',
            '#10#' => 'Октября',
            '#11#' => 'Ноября',
            '#12#' => 'Декабря',
        ];
        preg_match('|#\d{1,2}#|', $date, $matches);
        if (!empty($matches[0])) {
            return str_replace($matches[0], $months[$matches[0]], $date);
        }
        
        return $date;
    }
}
