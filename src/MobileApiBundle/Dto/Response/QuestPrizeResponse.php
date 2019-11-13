<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Quest\Prize;
use JMS\Serializer\Annotation as Serializer;

class QuestPrizeResponse
{
    /**
     * @Serializer\SerializedName("promocode")
     * @Serializer\Type("string")
     * @var string
     */
    protected $promocode = '';

    /**
     * @Serializer\SerializedName("user_prize")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Quest\Prize")
     * @var Prize
     */
    protected $userPrize;

    /**
     * @return string
     */
    public function getPromocode(): string
    {
        return $this->promocode;
    }

    /**
     * @param string $promocode
     * @return QuestPrizeResponse
     */
    public function setPromocode(string $promocode): QuestPrizeResponse
    {
        $this->promocode = $promocode;
        return $this;
    }

    /**
     * @return Prize
     */
    public function getUserPrize(): Prize
    {
        return $this->userPrize;
    }

    /**
     * @param Prize $userPrize
     * @return QuestPrizeResponse
     */
    public function setUserPrize(Prize $userPrize): QuestPrizeResponse
    {
        $this->userPrize = $userPrize;
        return $this;
    }
}
