<?php

namespace FourPaws\CatalogBundle\Dto\Dostavista;

use Doctrine\Common\Annotations\Annotation\Required;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Worktime
 *
 * @package FourPaws\CatalogBundle\Dto\Dostavista
 *
 * @Serializer\XmlRoot("worktime")
 */
class Worktime
{
    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Required()
     *
     * @var string
     */
    protected $day;

    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Required()
     * @Serializer\SerializedName("start-time")
     *
     * @var string
     */
    protected $startTime;

    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Required()
     * @Serializer\SerializedName("end-time")
     *
     * @var string
     */
    protected $endTime;

    /**
     * @return string
     */
    public function getDay(): string
    {
        return $this->day;
    }

    /**
     * @param string $day
     * @return Worktime
     */
    public function setDay(string $day): Worktime
    {
        $this->day = $day;

        return $this;
    }

    /**
     * @return string
     */
    public function getStartTime(): string
    {
        return $this->startTime;
    }

    /**
     * @param string $startTime
     * @return Worktime
     */
    public function setStartTime(string $startTime): Worktime
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndTime(): string
    {
        return $this->endTime;
    }

    /**
     * @param string $endTime
     * @return Worktime
     */
    public function setEndTime(string $endTime): Worktime
    {
        $this->endTime = $endTime;

        return $this;
    }
}
