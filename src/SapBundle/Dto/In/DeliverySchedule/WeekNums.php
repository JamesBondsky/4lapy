<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\DeliverySchedule;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class WeekNums
 *
 * @package FourPaws\SapBundle\Dto\In
 * @Serializer\XmlRoot("weeknums")
 */
class WeekNums
{
    /**
     * @Serializer\XmlList(inline=true, entry="numweek")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\DeliverySchedule\NumWeek>")
     *
     * @var Collection|NumWeek[]
     */
    protected $weekNums;

    /**
     * @return Collection|NumWeek[]
     */
    public function getWeekNums()
    {
        return $this->weekNums;
    }

    /**
     * @param Collection $weekNums
     * @return WeekNums
     */
    public function setWeekNums(Collection $weekNums): WeekNums
    {
        $this->weekNums = $weekNums;

        return $this;
    }
}