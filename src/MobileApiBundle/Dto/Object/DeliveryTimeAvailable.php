<?php

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class DeliveryTimeAvailable
 * @package FourPaws\MobileApiBundle\Dto\Object
 */
class DeliveryTimeAvailable
{
    /**
     * @Serializer\Type("DateTime<'d.m.Y'>")
     * @Serializer\SerializedName("day")
     * @var \DateTime
     */
    protected $day;

    /**
     * @Serializer\Type("DateTime<'H:i'>")
     * @Serializer\SerializedName("time")
     * @var \DateTime
     */
    protected $time;

    public function __construct(\DateTime $dateTime)
    {
        $this->day = $dateTime;
        $this->time = $dateTime;
    }

    /**
     * @return string
     */
    public function getDay(): string
    {
        return $this->day;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }
}
