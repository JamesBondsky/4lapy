<?php

namespace FourPaws\External\Manzana\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class Card
 *
 * @package FourPaws\External\Manzana\Model
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("Card")
 */
class Card
{
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("CardNumber")
     */
    public $cardNumber;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("contactid")
     */
    public $contactId;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("FirstName")
     */
    public $firstName;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("MiddleName")
     */
    public $secondName;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("lastname")
     */
    public $lastName;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("gendercode")
     */
    public $genderCode;
    
    /**
     * @Type("manzana_date_time_short")
     * @SerializedName("BirthDate")
     */
    public $birthDate;
    
    /**
     * Поле familystatuscode отвечает за участие контакта в бонусной программе
     * 2 - контакт участвует в бонусной программе
     *
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("FamilyStatusCode")
     */
    public $familyStatusCode;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("emailaddress1")
     */
    public $email;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("mobilephone")
     */
    public $phone;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("telephone1")
     */
    public $phoneAdditional1;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("telephone2")
     */
    public $phoneAdditional2;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("address1_postalcode")
     */
    public $addressPostalCode;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("address1_stateorprovince")
     */
    public $addressStateOrProvince;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("address1_city")
     */
    public $addressCity;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("address1_line1")
     */
    public $address;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("address1_line2")
     */
    public $addressLine2;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("Address1_Line3")
     */
    public $addressLine3;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_address1_flat")
     */
    public $plAddressFlat;
    
    /**
     * @Type("manzana_date_time_short")
     * @SerializedName("pl_registration_date")
     */
    public $plRegistrationDate;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_login")
     */
    public $plLogin;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_shopsnameName")
     */
    public $plShopsName;
    
    /**
     * @Type("float")
     * @SerializedName("pl_debet")
     */
    public $plDebet;
    
    /**
     * @Type("float")
     * @SerializedName("pl_credit")
     */
    public $plCredit;
    
    /**
     * @Type("float")
     * @SerializedName("pl_balance")
     */
    public $plBalance;
    
    /**
     * @Type("float")
     * @SerializedName("pl_active_balance")
     */
    public $plActiveBalance;
    
    /**
     * @Type("float")
     * @SerializedName("pl_summ")
     */
    public $plSumm;
    
    /**
     * @Type("float")
     * @SerializedName("pl_summdiscounted")
     */
    public $plSummDiscounted;
    
    /**
     * @Type("float")
     * @SerializedName("pl_discountsumm")
     */
    public $plDiscountSumm;
    
    /**
     * @Type("int")
     * @SerializedName("pl_quantity")
     */
    public $plQuantity;
    
    /**
     * Поле haschildrencode отвечает за актуальность контакта
     * 200000 - контакт актуален
     *
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("haschildrencode")
     */
    public $hashChildrenCode;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_codeword")
     */
    public $plCodeWord;
    
    /**
     * @Type("float")
     * @SerializedName("BalanceActionLimit")
     */
    public $balanceActionLimit;
    
    /**
     * @Type("float")
     * @SerializedName("BalanceActionNoLimit")
     */
    public $balanceActionNoLimit;
    
    /**
     * @Type("float")
     * @SerializedName("BalanceExtraLimit")
     */
    public $balanceExtraLimit;
    
    /**
     * @Type("float")
     * @SerializedName("BalanceExtraNoLimit")
     */
    public $balanceExtraNoLimit;
    
    /**
     * @Type("float")
     * @SerializedName("EmployeeBreeder")
     */
    public $employeeBreeder;
    
    /**
     * @return bool
     */
    public function isActualContact() : bool
    {
        return (int)$this->hashChildrenCode === 200000;
    }
    
    /**
     * @return bool
     */
    public function isLoyaltyProgramContact() : bool
    {
        return (int)$this->familyStatusCode === 2;
    }
    
    /**
     * @return bool
     */
    public function isBonusCard() : bool
    {
        // товарищи из манзаны гарантируют: ненулевой pl_debet <=> карта бонусная
        return (double)$this->plDebet > 0;
    }
}
