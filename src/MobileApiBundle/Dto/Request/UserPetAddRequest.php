<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Parts\Pet;
use FourPaws\MobileApiBundle\Dto\Request\Types\PostRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class UserPetAddRequest implements SimpleUnserializeRequest, PostRequest
{
    use Pet;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("gender")
     * @Assert\NotBlank()
     * @var string
     */
    protected $gender;

    /**
     * @return string
     */
    public function getGender(): string
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     * @return $this
     */
    public function setGender(string $gender)
    {
        $this->gender = $gender;
        return $this;
    }
}
