<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Entity;

class Schedule
{
    public const PATTERN = '~^(\d{1,2}):00\D+(\d{1,2}):00$~';

    public const STRING_PATTERN = '%s:00 - %s:00';

    /** @var int */
    protected $from = 0;

    /** @var int */
    protected $to = 0;

    public function __construct(string $schedule = '')
    {
        preg_match(static::PATTERN, $schedule, $matches);
        if (!empty($matches)) {
            $this->setFrom((int)$matches[1]);
            $this->setTo((int)$matches[2]);
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
    public function __toString(): string
    {
        if (($this->getFrom() === 0) && ($this->getTo() === 0)) {
            $result = 'Круглосуточно';
        } else {
            $result = sprintf(static::STRING_PATTERN, $this->getFrom(), $this->getTo());
        }

        return $result;
    }
}
