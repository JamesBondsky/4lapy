<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class UserPetDeleteRequest implements SimpleUnserializeRequest, DeleteRequest
{
    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     * @var int
     */
    protected $id;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
