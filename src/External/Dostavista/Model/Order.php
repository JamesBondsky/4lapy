<?php

namespace FourPaws\External\Dostavista\Model;

use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;
use Doctrine\Common\Collections\Collection;

/**
 * Class Order
 *
 * @package FourPaws\External\Dostavista\Model
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("Order")
 */
class Order
{
    /**
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("bitrix_order_id")
     */
    public $bitrixOrderId;

    /**
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("total_weight_kg")
     */
    public $totalWeightKg;

    /**
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("vehicle_type_id")
     */
    public $vehicleTypeId;

    /**
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("loaders_count")
     */
    public $loadersCount;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("matter")
     */
    public $matter;

    /**
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("insurance_amount")
     */
    public $insuranceAmount;

    /**
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("is_client_notification_enabled")
     */
    public $isClientNotificationEnabled;

    /**
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("is_contact_person_notification_enabled")
     */
    public $isContactPersonNotificationEnabled;

    /**
     * @Serializer\XmlList(inline=false, entry="Point")
     * @Serializer\SerializedName("points")
     *
     * @var Point[]|Collection
     */
    public $points;
}
