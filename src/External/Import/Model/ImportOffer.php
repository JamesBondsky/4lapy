<?php


namespace FourPaws\External\Import\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class ImportOffer
 * @package FourPaws\External\Import\Model
 */
class ImportOffer
{
    /**
     * @Type("string")
     * @SerializedName("offerId")
     */
    public $offerId;

    /**
     * @Type("string")
     * @SerializedName("promoCode")
     */
    public $promoCode;

    /**
     * @Type("array")
     * @SerializedName("users")
     */
    public $users;

    /**
     * @Type("Bitrix\Main\Type\DateTime")
     * @SerializedName("dateCreate")
     */
    public $dateCreate;

    /**
     * @Type("Bitrix\Main\Type\DateTime")
     * @SerializedName("dateChanged")
     */
    public $dateChanged;
}
