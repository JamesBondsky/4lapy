<?php

namespace FourPaws\External\Manzana\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class Payment
 *
 * @package FourPaws\External\Manzana\Model
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("Payment")
 */
class Payment
{
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("Value")
     */
    public $value;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("TypeName")
     */
    public $typeName;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("ExternalId")
     */
    public $externalId;
}