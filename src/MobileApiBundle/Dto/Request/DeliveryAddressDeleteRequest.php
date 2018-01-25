<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class DeliveryAddressDeleteRequest
{
    /**
     * @var int
     * @Assert\NotBlank()
     * @Serializer\SerializedName("id")
     * @Serializer\Type("string")
     */
    protected $id = '';

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
