<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

/**
 * Статус заказа
 * Class OrderStatus
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class OrderStatus
{
    /**
     * @Serializer\SerializedName("code")
     * @Serializer\Type("string")
     * @var string
     */
    protected $code = '';

    /**
     * @Serializer\SerializedName("title")
     * @Serializer\Type("string")
     * @var string
     */
    protected $title = '';

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return OrderStatus
     */
    public function setCode(string $code): OrderStatus
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return OrderStatus
     */
    public function setTitle(string $title): OrderStatus
    {
        $this->title = $title;
        return $this;
    }
}
