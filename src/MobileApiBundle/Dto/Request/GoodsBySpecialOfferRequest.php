<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class GoodsBySpecialOfferRequest implements SimpleUnserializeRequest, GetRequest
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
    public function setId(string $id): GoodsBySpecialOfferRequest
    {
        $this->id = $id;
        return $this;
    }
}
