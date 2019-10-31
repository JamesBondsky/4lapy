<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

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
}
