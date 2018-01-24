<?php

namespace FourPaws\External\Manzana\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class CardValidateResult
 *
 * @package FourPaws\External\Manzana\Model
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("cardvalidateresult")
 */
class CardValidateResult
{
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("isvalid")
     */
    public $isValid;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("validationresult")
     */
    public $validationResult;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("FirstName")
     */
    public $firstName;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("validationresultcode")
     */
    public $validationResultCode;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("cardid")
     */
    public $cardId;
}
