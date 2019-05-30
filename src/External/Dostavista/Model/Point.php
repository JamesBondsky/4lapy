<?php

namespace FourPaws\External\Dostavista\Model;

use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\SerializedName;
use Doctrine\Common\Collections\Collection;

/**
 * Class Point
 *
 * @package FourPaws\External\Dostavista\Model
 *
 * @Serializer\XmlRoot("point")
 */
class Point
{
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("address")
     */
    public $address;

    /**
     * @XmlElement(cdata=false)
     * @SerializedName("contact_person")
     *
     * @var ContactPerson[]|Collection
     */
    public $contactPerson;

    /**
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("client_order_id")
     */
    public $clientOrderId;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("required_start_datetime")
     */
    public $requiredStartDatetime;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("required_finish_datetime")
     */
    public $requiredFinishDatetime;

    /**
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("taking_amount")
     */
    public $takingAmount;

    /**
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("buyout_amount")
     */
    public $buyoutAmount;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("note")
     */
    public $note;
}
