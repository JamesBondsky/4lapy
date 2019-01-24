<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class PayRequest implements SimpleUnserializeRequest, PostRequest
{
    /**
     * Номер заказа
     * @Serializer\Type("int")
     * @Serializer\SerializedName("order_id")
     * @Assert\NotBlank()
     * @var int
     */
    protected $orderNumber;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("payType")
     * @Assert\NotBlank()
     * @Assert\Choice({"cash", "cashless", "applepay", "android"})
     * @var string
     */
    protected $payType;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("payToken")
     * @var string
     */
    protected $payToken = '';

    /**
     * @return int
     */
    public function getOrderNumber(): int
    {
        return $this->orderNumber;
    }

    /**
     * @return string
     */
    public function getPayType(): string
    {
        return $this->payType;
    }

    /**
     * @return string
     */
    public function getPayToken(): string
    {
        return $this->payToken ?: '';
    }
}
