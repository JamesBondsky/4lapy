<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

class DeliveryTime
{
    /**
     * id интервала в рамках одного дня
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     * @var int
     */
    protected $id;

    /**
     * @Serializer\Type("DateTime<d.m.Y>")
     * @Serializer\SerializedName("delivery_date")
     * @var \DateTime
     */
    protected $deliveryDate;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     * @var string
     */
    protected $title = '';

    /**
     * @Serializer\SerializedName("available")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\DeliveryTimeAvailable")
     * @var DeliveryTimeAvailable
     */
    protected $available;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return DeliveryTime
     */
    public function setId(int $id): DeliveryTime
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDeliveryDate(): \DateTime
    {
        return $this->deliveryDate;
    }

    /**
     * @param \DateTime $deliveryDate
     * @return DeliveryTime
     */
    public function setDeliveryDate(\DateTime $deliveryDate): DeliveryTime
    {
        $this->deliveryDate = $deliveryDate;
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
     * @return DeliveryTime
     */
    public function setTitle(string $title): DeliveryTime
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return DeliveryTimeAvailable
     */
    public function getAvailable(): DeliveryTimeAvailable
    {
        return $this->available;
    }

    /**
     * @param DeliveryTimeAvailable $available
     * @return DeliveryTime
     */
    public function setAvailable(DeliveryTimeAvailable $available): DeliveryTime
    {
        $this->available = $available;
        return $this;
    }
}
