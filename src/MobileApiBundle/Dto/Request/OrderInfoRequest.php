<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;

class OrderInfoRequest
{
    /**
     * @Serializer\SerializedName("id")
     * @Serializer\Type("string")
     * @var string
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
    public function setId(string $id): OrderInfoRequest
    {
        $this->id = $id;
        return $this;
    }
}
