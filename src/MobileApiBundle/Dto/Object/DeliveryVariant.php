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
}
