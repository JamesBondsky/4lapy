<?php

namespace FourPaws\External\Manzana\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class Referrals
 *
 * @package FourPaws\External\Manzana\Model
 *
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("ContactReferalCards")
 */
class Referrals
{
    /**
     * @Type("ArrayCollection<FourPaws\External\Manzana\Model\Referral>")
     * @XmlList(entry="Referral_Cards", inline=true)
     * @SerializedName("Referral_Cards")
     */
    public $referrals;
    /**
     * @Type("string")
     * @XmlElement(cdata=false)
     * @SerializedName("ContactID")
     */
    public $contactId;
}
