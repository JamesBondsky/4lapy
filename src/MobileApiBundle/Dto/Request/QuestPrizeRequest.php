<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class QuestPrizeRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\SerializedName("prize_id")
     * @Serializer\Type("int")
     * @Assert\NotBlank()
     * @var $prizeId
     */
    protected $prizeId;

    /**
     * @return mixed
     */
    public function getPrizeId()
    {
        return $this->prizeId;
    }

    /**
     * @param mixed $prizeId
     * @return QuestPrizeRequest
     */
    public function setPrizeId($prizeId): QuestPrizeRequest
    {
        $this->prizeId = $prizeId;
        return $this;
    }
}
