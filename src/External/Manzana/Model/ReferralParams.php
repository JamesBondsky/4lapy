<?php

namespace FourPaws\External\Manzana\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class Referral
 *
 * @package FourPaws\External\Manzana\Model
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("Referral_Card")
 */
class ReferralParams
{
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("cardnumber")
     */
    public $cardNumber;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("contact_id")
     */
    public $contactId;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("firstname")
     */
    public $name;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("lastname")
     */
    public $lastName;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("middlename")
     */
    public $secondName;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("mobilephone")
     */
    public $phone;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("emailaddress1")
     */
    public $email;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("birthdate")
     */
    public $birthday;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("gendercode")
     */
    public $gender;
}
