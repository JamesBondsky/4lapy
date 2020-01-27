<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class OrderInfoRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * Номер заказа
     * @Serializer\SerializedName("id")
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     * @var string
     */
    protected $orderNumber;

    /**
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     * @return OrderInfoRequest
     */
    public function setOrderNumber(string $orderNumber): OrderInfoRequest
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }
}
