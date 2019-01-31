<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\PersonalBonus;
use JMS\Serializer\Annotation as Serializer;

class PersonalBonusResponse
{
    /**
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\PersonalBonus")
     * @Serializer\SerializedName("bonus")
     * @var PersonalBonus
     */
    protected $bonus;

    /**
     * @return PersonalBonus
     */
    public function getPersonalBonus(): PersonalBonus
    {
        return $this->bonus;
    }

    /**
     * @param PersonalBonus $bonus
     * @return PersonalBonusResponse
     */
    public function setPersonalBonus(PersonalBonus $bonus): PersonalBonusResponse
    {
        $this->bonus = $bonus;
        return $this;
    }
}
