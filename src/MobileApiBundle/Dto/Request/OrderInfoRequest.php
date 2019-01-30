<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class OrderInfoRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Type("int")
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

    /**
     * @param int $id
     * @return OrderInfoRequest
     */
    public function setId(int $id): OrderInfoRequest
    {
        $this->id = $id;
        return $this;
    }
}
