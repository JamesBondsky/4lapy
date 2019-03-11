<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\DeliverySchedule;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class OrderDayItem
 *
 * @package FourPaws\SapBundle\Dto\In
 * @Serializer\XmlRoot("orderdays")
 */
class OrderDayItem
{
    /**
     * Понедельник.
     * Если чекбокс установлен, поставка осуществляется.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Monday")
     *
     * @var int
     */
    protected $monday = 0;

    /**
     * Вторник.
     * Если чекбокс установлен, поставка осуществляется.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Tuesday")
     *
     * @var int
     */
    protected $tuesday = 0;

    /**
     * Среда.
     * Если чекбокс установлен, поставка осуществляется.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Wednesday")
     *
     * @var int
     */
    protected $wednesday = 0;

    /**
     * Четверг.
     * Если чекбокс установлен, поставка осуществляется.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Thursday")
     *
     * @var int
     */
    protected $thursday = 0;

    /**
     * Пятница.
     * Если чекбокс установлен, поставка осуществляется.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Friday")
     *
     * @var int
     */
    protected $friday = 0;

    /**
     * Суббота.
     * Если чекбокс установлен, поставка осуществляется.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Saturday")
     *
     * @var int
     */
    protected $saturday = 0;

    /**
     * Воскресенье.
     * Если чекбокс установлен, поставка осуществляется.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("Sunday")
     *
     * @var int
     */
    protected $sunday = 0;

    /**
     * @return int
     */
    public function isMonday(): int
    {
        return $this->monday;
    }

    /**
     * @param int $monday
     * @return OrderDayItem
     */
    public function setMonday(int $monday): OrderDayItem
    {
        $this->monday = $monday;
        return $this;
    }

    /**
     * @return int
     */
    public function isTuesday(): int
    {
        return $this->tuesday;
    }

    /**
     * @param int $tuesday
     * @return OrderDayItem
     */
    public function setTuesday(int $tuesday): OrderDayItem
    {
        $this->tuesday = $tuesday;
        return $this;
    }

    /**
     * @return int
     */
    public function isWednesday(): int
    {
        return $this->wednesday;
    }

    /**
     * @param int $wednesday
     * @return OrderDayItem
     */
    public function setWednesday(int $wednesday): OrderDayItem
    {
        $this->wednesday = $wednesday;
        return $this;
    }

    /**
     * @return int
     */
    public function isThursday(): int
    {
        return $this->thursday;
    }

    /**
     * @param int $thursday
     * @return OrderDayItem
     */
    public function setThursday(int $thursday): OrderDayItem
    {
        $this->thursday = $thursday;
        return $this;
    }

    /**
     * @return int
     */
    public function isFriday(): int
    {
        return $this->friday;
    }

    /**
     * @param int $friday
     * @return OrderDayItem
     */
    public function setFriday(int $friday): OrderDayItem
    {
        $this->friday = $friday;
        return $this;
    }

    /**
     * @return int
     */
    public function isSaturday(): int
    {
        return $this->saturday;
    }

    /**
     * @param int $saturday
     * @return OrderDayItem
     */
    public function setSaturday(int $saturday): OrderDayItem
    {
        $this->saturday = $saturday;
        return $this;
    }

    /**
     * @return int
     */
    public function isSunday(): int
    {
        return $this->sunday;
    }

    /**
     * @param int $sunday
     * @return OrderDayItem
     */
    public function setSunday(int $sunday): OrderDayItem
    {
        $this->sunday = $sunday;
        return $this;
    }

}
