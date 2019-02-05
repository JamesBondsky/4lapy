<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class UserPetPhotoAddRequest implements SimpleUnserializeRequest, PostRequest
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
     * @Serializer\Type("string")
     * @Serializer\SerializedName("photo")
     * @Assert\NotBlank()
     * @var string
     */
    protected $photo;

    /**
     * @return int
     */
    public function getPetId(): int
    {
        return $this->petId;
    }

    /**
     * @return string
     */
    public function getPhoto(): string
    {
        return $this->photo;
    }

}
