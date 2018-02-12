<?php

namespace FourPaws\External\Manzana\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class ChequesByContractCheques
 * Результат метода getChequesByCardId (cheques)
 *
 * @package FourPaws\External\Manzana\Model
 *
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("Cheques")
 */
class ChequesByContractCheques
{
    /**
     * @Type("ArrayCollection<FourPaws\External\Manzana\Model\ChequeByContractCheques>")
     * @XmlList(entry="Cheque", inline=true)
     * @SerializedName("Cheques")
     */
    public $cheques;
}
