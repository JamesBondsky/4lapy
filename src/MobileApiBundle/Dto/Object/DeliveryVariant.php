<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

class DeliveryVariant
{
    /**
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("available")
     * @var bool
     */
    protected $available = false;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("date")
     * @var string
     */
    protected $date = '';

    /**
     * @Serializer\Type("float")
     * @Serializer\SerializedName("price")
     * @var float
     */
    protected $price = 0.0;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("shortDate")
     * @var string
     */
    protected $shortDate = '';


    /**
     * @return bool
     */
    public function getAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @param bool $available
     * @return DeliveryVariant
     */
    public function setAvailable(bool $available): DeliveryVariant
    {
        $this->available = $available;
        return $this;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @return DeliveryVariant
     */
    public function setDate(string $date): DeliveryVariant
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return DeliveryVariant
     */
    public function setPrice(float $price): DeliveryVariant
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return string
     */
    public function getShortDate(): string
    {
        return $this->shortDate;
    }

    /**
     * @param string $shortDate
     * @return DeliveryVariant
     */
    public function setShortDate(string $shortDate): DeliveryVariant
    {
        $this->shortDate = $shortDate;
        return $this;
    }
}
