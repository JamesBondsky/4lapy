<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\DeliverySchedule;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class NumWeek
 *
 * @package FourPaws\SapBundle\Dto\In
 * @Serializer\XmlRoot("numweek")
 */
class NumWeek
{
    /**
     * @Serializer\XmlValue()
     * @Serializer\Type("string")
     * @var $value
     */
    protected $value;

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return NumWeek
     */
    public function setValue($value): NumWeek
    {
        $this->value = $value;

        return $this;
    }
}