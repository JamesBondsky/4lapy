<?php

namespace FourPaws\External\Manzana\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class Referral
 *
 * @package FourPaws\External\Manzana\Model
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("Referral_Card")
 */
class Referral
{
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("card_number")
     */
    public $cardNumber;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("referral_number")
     */
    public $referralNumber;
    
    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("sum_referral_bonus")
     */
    public $sumReferralBonus;
    
    /**
     * Актуальность реферала
     * 1 - Не указано, 2000 - Да, 2001 - Нет
     * @XmlElement(cdata=false)
     * @Type("int")
     * @SerializedName("is_questionnaire_actual")
     */
    public $isQuestionnaireActual;

    /**
     * @return bool
     */
    public function isModerated(): bool
    {
        return $this->isQuestionnaireActual === 1;
    }

    /**
     * @return bool
     */
    public function isSuccessModerate(): bool
    {
        return $this->isQuestionnaireActual === 2000;
    }

    /**
     * @return bool
     */
    public function isCancelModerate(): bool
    {
        return $this->isQuestionnaireActual === 2001;
    }
}
