<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\DeleteRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class DeliveryAddressDeleteRequest implements DeleteRequest, SimpleUnserializeRequest
{
    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\GreaterThan("0")
     * @Serializer\SerializedName("id")
     * @Serializer\Type("string")
     */
    protected $id = 0;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return DeliveryAddressDeleteRequest
     */
    public function setId(int $id): DeliveryAddressDeleteRequest
    {
        $this->id = $id;
        return $this;
    }
}
