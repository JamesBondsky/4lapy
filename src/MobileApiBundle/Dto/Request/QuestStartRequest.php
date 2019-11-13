<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class QuestStartRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * @Serializer\SerializedName("pet_type_id")
     * @Serializer\Type("int")
     * @Assert\NotBlank()
     * @var $petTypeId
     */
    protected $petTypeId;

    /**
     * @return mixed
     */
    public function getPetTypeId()
    {
        return $this->petTypeId;
    }

    /**
     * @param mixed $petTypeId
     * @return QuestStartRequest
     */
    public function setPetTypeId($petTypeId): QuestStartRequest
    {
        $this->petTypeId = $petTypeId;
        return $this;
    }
}
