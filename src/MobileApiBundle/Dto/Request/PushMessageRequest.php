<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\DeleteRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\PutRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class PushMessageRequest implements SimpleUnserializeRequest, PutRequest, DeleteRequest
{
    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Type("string")
     * @var string
     * @Assert\NotBlank();
     */
    protected $id = '';

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return OrderInfoRequest
     */
    public function setId(string $id): PushMessageRequest
    {
        $this->id = $id;
        return $this;
    }
}
