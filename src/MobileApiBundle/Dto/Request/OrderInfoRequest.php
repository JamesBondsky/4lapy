<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class OrderInfoRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * Номер заказа
     * @Serializer\SerializedName("id")
     * @Serializer\Type("int")
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     * @var int
     */
    protected $orderNumber;

    /**
     * @return int
     */
    public function getOrderNumber(): int
    {
        return $this->orderNumber;
    }

    /**
     * @param int $orderNumber
     * @return OrderInfoRequest
     */
    public function setOrderNumber(int $orderNumber): OrderInfoRequest
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }
}
