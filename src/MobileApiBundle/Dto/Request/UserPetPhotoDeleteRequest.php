<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class UserPetPhotoDeleteRequest implements SimpleUnserializeRequest, DeleteRequest
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
