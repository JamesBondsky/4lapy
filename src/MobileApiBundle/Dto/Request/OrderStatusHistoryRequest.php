<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;

class OrderStatusHistoryRequest
{
    /**
     * id заказа
     * @Serializer\Type("string")
     * @Serializer\SerializedName("id")
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
     * @return OrderStatusHistoryRequest
     */
    public function setId(string $id): OrderStatusHistoryRequest
    {
        $this->id = $id;
        return $this;
    }
}
