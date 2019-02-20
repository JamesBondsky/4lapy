<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

class DeliveryTime
{
    /**
     * @Serializer\Type("DateTime<'d.m.Y'>")
     * @Serializer\SerializedName("deliveryDate")
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
     * @Serializer\Type("int")
     * @Serializer\SerializedName("deliveryDateIndex")
     * @var int
     */
    protected $deliveryDateIndex;

    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("deliveryIntervalIndex")
     * @var int
     */
    protected $deliveryIntervalIndex;

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
     * @return int
     */
    public function getDeliveryDateIndex(): int
    {
        return $this->deliveryDateIndex;
    }

    /**
     * @param int $deliveryDateIndex
     * @return DeliveryTime
     */
    public function setDeliveryDateIndex(int $deliveryDateIndex): DeliveryTime
    {
        $this->deliveryDateIndex = $deliveryDateIndex;
        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryIntervalIndex(): int
    {
        return $this->deliveryIntervalIndex;
    }

    /**
     * @param int $deliveryIntervalIndex
     * @return DeliveryTime
     */
    public function setDeliveryIntervalIndex(int $deliveryIntervalIndex): DeliveryTime
    {
        $this->deliveryIntervalIndex = $deliveryIntervalIndex;
        return $this;
    }
}
