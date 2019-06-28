<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Entity;

class Schedule
{
    public const PATTERN = '~^(\d{1,2}):(\d{1,2})\D+(\d{1,2}):(\d{1,2})$~';

    public const STRING_PATTERN = '%s:%s - %s:%s';

    /** @var int */
    protected $from = 0;

    /** @var int */
    protected $to = 0;

    /** @var string */
    protected $fromMinutes = 0;

    /** @var string */
    protected $toMinutes = 0;

    public function __construct(string $schedule = '')
    {
        preg_match(static::PATTERN, $schedule, $matches);
        if (!empty($matches)) {
            $this->setFrom((int)$matches[1]);
            $this->setFromMinutes((string)$matches[2]);
            $this->setTo((int)$matches[3]);
            $this->setToMinutes((string)$matches[4]);
        }
    }

    /**
     * @return int
     */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * @param int $from
     * @return Schedule
     */
    public function setFrom(int $from): Schedule
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return int
     */
    public function getTo(): int
    {
        return $this->to;
    }

    /**
     * @param int $to
     * @return Schedule
     */
    public function setTo(int $to): Schedule
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromMinutes(): string
    {
        return $this->fromMinutes;
    }

    /**
     * @param string $fromMinutes
     * @return Schedule
     */
    public function setFromMinutes(string $fromMinutes): Schedule
    {
        $this->fromMinutes = $fromMinutes;
        return $this;
    }

    /**
     * @return string
     */
    public function getToMinutes(): string
    {
        return $this->toMinutes;
    }

    /**
     * @param string $toMinutes
     * @return Schedule
     */
    public function setToMinutes(string $toMinutes): Schedule
    {
        $this->toMinutes = $toMinutes;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (($this->getFrom() === 0) && ($this->getTo() === 0)) {
            $result = 'Круглосуточно';
        } else {
            $result = sprintf(static::STRING_PATTERN, $this->getFrom(), $this->getFromMinutes(), $this->getTo(), $this->getToMinutes());
        }

        return $result;
    }
}
