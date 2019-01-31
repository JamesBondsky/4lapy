<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class OrderStatusHistoryRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * Номер заказа
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
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
     * @return OrderStatusHistoryRequest
     */
    public function setOrderNumber(int $orderNumber): OrderStatusHistoryRequest
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }
}
