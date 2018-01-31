<?php

namespace FourPaws\External\Manzana\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class Card
 *
 * @package FourPaws\External\Manzana\Model
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("ChequeItem")
 */
class ChequeItem
{
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("ChequeId")
     */
    public $chequeId;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("ArticleName")
     */
    public $name;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("ArticleNumber")
     */
    public $number;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("Quantity")
     */
    public $quantity;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("Price")
     */
    public $price;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("Discount")
     */
    public $discount;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("Summ")
     */
    public $sum;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("SummDiscounted")
     */
    public $sumDiscounted;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("ItemUrl")
     */
    public $url;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("Bonus")
     */
    public $bonus;
}