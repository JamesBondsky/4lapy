<?php

namespace FourPaws\SapBundle\Dto\In\DeliverySchedule;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class WeekDayItem
 *
 * @package FourPaws\SapBundle\Dto\In
 * @Serializer\XmlRoot("weekdays")
  */
class WeekDayItem
{
    /**
     * Номер недели.
     * Содержит порядковый номер недели в году, для которой определен график поставок по дням недели.
     * Если значение типа графика поставок «2», поле должно быть заполнено.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("numweek")
     *
     * @var int
     */
    protected $numWeek = 0;

    /**
     * Понедельник.
     * Если чекбокс установлен, поставка осуществляется.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("sap_bool")
     * @Serializer\SerializedName("Monday")
     *
     * @var bool
     */
    protected $monday = false;

    /**
     * Вторник.
     * Если чекбокс установлен, поставка осуществляется.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("sap_bool")
     * @Serializer\SerializedName("Tuesday")
     *
     * @var bool
     */
    protected $tuesday = false;

    /**
     * Среда.
     * Если чекбокс установлен, поставка осуществляется.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("sap_bool")
     * @Serializer\SerializedName("Wednesday")
     *
     * @var bool
     */
    protected $wednesday = false;

    /**
     * Четверг.
     * Если чекбокс установлен, поставка осуществляется.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("sap_bool")
     * @Serializer\SerializedName("Thursday")
     *
     * @var bool
     */
    protected $thursday = false;

    /**
     * Пятница.
     * Если чекбокс установлен, поставка осуществляется.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("sap_bool")
     * @Serializer\SerializedName("Friday")
     *
     * @var bool
     */
    protected $friday = false;

    /**
     * Суббота.
     * Если чекбокс установлен, поставка осуществляется.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("sap_bool")
     * @Serializer\SerializedName("Saturday")
     *
     * @var bool
     */
    protected $saturday = false;

    /**
     * Воскресенье.
     * Если чекбокс установлен, поставка осуществляется.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("sap_bool")
     * @Serializer\SerializedName("Sunday")
     *
     * @var bool
     */
    protected $sunday = false;

    /**
     * @return int
     */
    public function getNumWeek(): int
    {
        return $this->numWeek;
    }

    /**
     * @param int $numWeek
     * @return WeekDayItem
     */
    public function setNumWeek(int $numWeek): WeekDayItem
    {
        $this->numWeek = $numWeek;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMonday(): bool
    {
        return $this->monday;
    }

    /**
     * @param bool $monday
     * @return WeekDayItem
     */
    public function setMonday(bool $monday): WeekDayItem
    {
        $this->monday = $monday;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTuesday(): bool
    {
        return $this->tuesday;
    }

    /**
     * @param bool $tuesday
     * @return WeekDayItem
     */
    public function setTuesday(bool $tuesday): WeekDayItem
    {
        $this->tuesday = $tuesday;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWednesday(): bool
    {
        return $this->wednesday;
    }

    /**
     * @param bool $wednesday
     * @return WeekDayItem
     */
    public function setWednesday(bool $wednesday): WeekDayItem
    {
        $this->wednesday = $wednesday;
        return $this;
    }

    /**
     * @return bool
     */
    public function isThursday(): bool
    {
        return $this->thursday;
    }

    /**
     * @param bool $thursday
     * @return WeekDayItem
     */
    public function setThursday(bool $thursday): WeekDayItem
    {
        $this->thursday = $thursday;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFriday(): bool
    {
        return $this->friday;
    }

    /**
     * @param bool $friday
     * @return WeekDayItem
     */
    public function setFriday(bool $friday): WeekDayItem
    {
        $this->friday = $friday;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSaturday(): bool
    {
        return $this->saturday;
    }

    /**
     * @param bool $saturday
     * @return WeekDayItem
     */
    public function setSaturday(bool $saturday): WeekDayItem
    {
        $this->saturday = $saturday;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSunday(): bool
    {
        return $this->sunday;
    }

    /**
     * @param bool $sunday
     * @return WeekDayItem
     */
    public function setSunday(bool $sunday): WeekDayItem
    {
        $this->sunday = $sunday;
        return $this;
    }
}
