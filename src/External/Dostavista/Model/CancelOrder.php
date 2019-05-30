<?php

namespace FourPaws\External\Dostavista\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class CancelOrder
 *
 * @package FourPaws\External\Dostavista\Model
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("CancelOrder")
 */
class CancelOrder
{
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("bitrix_order_id")
     */
    public $bitrixOrderId;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("dostavista_order_id")
     */
    public $dostavistaOrderId;
}
