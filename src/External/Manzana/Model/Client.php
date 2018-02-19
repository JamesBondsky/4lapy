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
 * Class Contact
 *
 * @package FourPaws\External\Manzana\Model
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("Client")
 */
class Client
{
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("contactid")
     */
    public $contactId;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("firstname")
     */
    public $firstName;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("middlename")
     */
    public $secondName;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("lastname")
     */
    public $lastName;

    /**
     * @Type("DateTimeImmutable<'Y-m-d\TH:i:s'>")
     * @SerializedName("birthdate")
     */
    public $birthDate;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("gendercode")
     */
    public $genderCode;

    /**
     * Поле familystatuscode отвечает за участие контакта в бонусной программе
     * 2 - контакт участвует в бонусной программе
     *
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("familystatuscode")
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
     * @SerializedName("pl_regionid")
     */
    public $plRegionId;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_regionname")
     */
    public $plRegionName;

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
     * @Type("int")
     * @SerializedName("donotemail")
     */
    public $doNotEmail;

    /**
     * @Type("int")
     * @SerializedName("pl_sendsms")
     */
    public $plSendSms;

    /**
     * @Type("int")
     * @SerializedName("pl_transliterate")
     */
    public $plTransliterate;

    /**
     * @Type("int")
     * @SerializedName("DoNotPostalMail")
     */
    public $doNotPostalMail;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_codeword")
     */
    public $plCodeWord;

    /**
     * @Type("int")
     * @SerializedName("PreferredContactMethodCode")
     */
    public $preferredContactMethodCode;

    /**
     * см. hasChildrenCode
     * (ML зачем-то возвращает два элемента: haschildrencode и HasChildrenCode)
     *
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("haschildrencode")
     */
    public $hashChildrenCode;

    /**
     * Поле haschildrencode отвечает за актуальность контакта
     * 200000 - контакт актуален
     *
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("HasChildrenCode")
     */
    public $hasChildrenCode;

    /**
     * @Type("int")
     * @SerializedName("pl_businessbranch")
     */
    public $plBusinessBranch;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_shopsname")
     */
    public $plShopsId;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_shopsnameName")
     */
    public $plShopsName;

    /**
     * @Type("DateTimeImmutable<'Y-m-d\TH:i:s'>")
     * @SerializedName("pl_registration_date")
     */
    public $plRegistrationDate;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_address1_line1_street_typeid")
     */
    public $plAddress1Line1StreetTypeId;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_address1_line1_street_typeidName")
     */
    public $plAddress1Line1StreetTypeName;

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
     * @Type("int")
     * @SerializedName("pl_source")
     */
    public $plSource;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("jobtitle")
     */
    public $jobTitle;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_login")
     */
    public $plLogin;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("SpousesName")
     */
    public $spousesName;

    /**
     * @Type("DateTimeImmutable<'Y-m-d\TH:i:s'>")
     * @SerializedName("Anniversary")
     */
    public $anniversary;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("OwnerId")
     */
    public $ownerId;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("OwnerIdName")
     */
    public $ownerIdName;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("cardnumber")
     */
    public $cardnumber;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_webarearole_externalId")
     */
    public $plWebAreaRoleExternalId;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_webarearoleid")
     */
    public $plWebAreaRoleId;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("pl_webarearole_name")
     */
    public $plWebAreaRoleName;

    /**
     * @Type("int")
     * @SerializedName("EmployeeBreeder")
     */
    public $employeeBreeder;

    /**
     * @Type("int")
     * @SerializedName("ff_cat")
     */
    public $ffCat;

    /**
     * @Type("int")
     * @SerializedName("ff_dog")
     */
    public $ffDog;

    /**
     * @Type("int")
     * @SerializedName("ff_bird")
     */
    public $ffBird;

    /**
     * @Type("int")
     * @SerializedName("ff_rodent")
     */
    public $ffRodent;

    /**
     * @Type("int")
     * @SerializedName("ff_fish")
     */
    public $ffFish;

    /**
     * @Type("int")
     * @SerializedName("ff_others")
     */
    public $ffOthers;

    /**
     * Код места активации карты
     *   UpdatedByСlient - ЛК покупателя (привязка карты в личном кабинете)
     *   Ishop - Страница регистрации карты
     *   UpdatedByСassa - ЛК магазина (касса)
     *   UpdatedByTab - ЛК магазина (планшет)
     *   UpdatedByanketa - ЛК магазина (ПК управляющего)
     *
     * @Type("string")
     * @SerializedName("ff_shopofactivation")
     */
    public $shopOfActivation;

    /**
     * Код места регистрации карты
     *   Ishop - Страница регистрации карты, ЛК покупателя (привязка карты в личном кабинете)
     *   Код магазина – RXXX - ЛК магазина
     *
     * @Type("string")
     * @SerializedName("ff_shopregistration")
     */
    public $shopRegistration;

    /**
     * @Type("ArrayCollection<FourPaws\External\Manzana\Model\Card>")
     * @XmlList(entry="Card", inline=true)
     * @SerializedName("Card")
     */
    public $cards;

    /**
     * @return bool
     */
    public function isActualContact() : bool
    {
        return $this->hasChildrenCode === 200000;
    }

    /**
     * @return bool
     */
    public function isLoyaltyProgramContact() : bool
    {
        return $this->familyStatusCode === 2;
    }

    /**
     * @param bool $isActualContact
     *
     * @return Client
     */
    public function setActualContact(bool $isActualContact = true) : Client
    {
        $this->hasChildrenCode = $isActualContact ? 200000 : 1;
        // для совместимости
        $this->hashChildrenCode = $this->hasChildrenCode;

        return $this;
    }

    /**
     * @param bool $isLoyaltyProgramContact
     *
     * @return Client
     */
    public function setLoyaltyProgramContact(bool $isLoyaltyProgramContact = true) : Client
    {
        $this->familyStatusCode = $isLoyaltyProgramContact ? 2 : 1;

        return $this;
    }
}
