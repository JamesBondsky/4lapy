<?php

namespace FourPaws\CatalogBundle\Dto\ExpertSender;

use Doctrine\Common\Annotations\Annotation\Required;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class DeliveryOption
 *
 * @package FourPaws\CatalogBundle\Dto\ExpertSender
 *
 * @Serializer\XmlRoot("option")
 */
class DeliveryOption
{
    /**
     * @Required()
     * @Serializer\XmlAttribute()
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $cost;

    /**
     * @Required()
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     *
     * @var int
     */
    protected $days;

    /**
     * @Serializer\SerializedName("order-before")
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SkipWhenEmpty()
     *
     * @var int
     */
    protected $daysBefore;

    /**
     * @Serializer\Exclude()
     *
     * @var int
     */
    protected $freeFrom;

    /**
     * @return float
     */
    public function getCost(): float
    {
        return $this->cost;
    }

    /**
     * @param float $cost
     *
     * @return DeliveryOption
     */
    public function setCost(float $cost): DeliveryOption
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * @return string
     */
    public function getDays(): string
    {
        return $this->days;
    }

    /**
     * @param string $days
     *
     * @return DeliveryOption
     */
    public function setDays(string $days): DeliveryOption
    {
        $this->days = $days;

        return $this;
    }

    /**
     * @return int
     */
    public function getDaysBefore(): int
    {
        return $this->daysBefore;
    }

    /**
     * @param int $daysBefore
     *
     * @return DeliveryOption
     */
    public function setDaysBefore(?int $daysBefore): DeliveryOption
    {
        $this->daysBefore = $daysBefore;

        return $this;
    }

    /**
     * @return int
     */
    public function getFreeFrom(): int
    {
        return $this->freeFrom;
    }

    /**
     * @param int $freeFrom
     *
     * @return $this
     */
    public function setFreeFrom(int $freeFrom): DeliveryOption
    {
        $this->freeFrom = $freeFrom;

        return $this;
    }
}
