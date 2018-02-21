<?php

namespace FourPaws\DeliveryBundle\Entity;

/**
 * Правило, добавляющее $value дней к дате доставке,
 * если время заказа лежит в промежутке между $from и $to
 *
 * Class IntervalRuleAddDays
 * @package FourPaws\DeliveryBundle\Entity
 */
class IntervalRuleAddDays extends IntervalRuleBase
{
    /**
     * @var string
     */
    protected $type = self::TYPE_ADD_DAYS;

    /**
     * @var int
     */
    protected $from;

    /**
     * @var int
     */
    protected $to;

    /**
     * @var int
     */
    protected $value;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
     *
     * @return IntervalRuleAddDays
     */
    public function setFrom(int $from): IntervalRuleAddDays
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
     *
     * @return IntervalRuleAddDays
     */
    public function setTo(int $to): IntervalRuleAddDays
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     *
     * @return IntervalRuleAddDays
     */
    public function setValue(int $value): IntervalRuleAddDays
    {
        $this->value = $value;

        return $this;
    }
}
