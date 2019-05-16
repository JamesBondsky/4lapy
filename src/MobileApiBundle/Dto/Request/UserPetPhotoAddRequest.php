<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class UserPetPhotoAddRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("pet_id")
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     * @var int
     */
    protected $petId;

    /**
     * @return int
     */
    public function getPetId(): int
    {
        return $this->petId;
    }

}
