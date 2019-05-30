<?php

namespace FourPaws\External\Dostavista\Model;

use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\SerializedName;

/**
 * Class ContactPerson
 *
 * @package FourPaws\External\Dostavista\Model
 *
 * @Serializer\XmlRoot("ContactPerson")
 */
class ContactPerson
{
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("phone")
     */
    public $phone;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("name")
     */
    public $name;
}
