<?php

namespace FourPaws\DeliveryBundle\Entity\IntervalRule;

use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;

/**
 * Правило, добавляющее $value дней к дате доставки,
 * если время заказа лежит в промежутке между $from и $to
 *
 * Class AddDaysRule
 * @package FourPaws\DeliveryBundle\Entity
 */
class AddDaysRule extends BaseRule implements TimeRuleInterface
{
    /**
     * @var string
     */
    protected $type = self::TYPE_ADD_DAYS;

    /**
     * @var int
     */
    protected $from = 0;

    /**
     * @var int
     */
    protected $to = 0;
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
     * @return AddDaysRule
     */
    public function setFrom(int $from): AddDaysRule
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
     * @return AddDaysRule
     */
    public function setTo(int $to): AddDaysRule
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
     * @return AddDaysRule
     */
    public function setValue(int $value): AddDaysRule
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @param \DateTime                  $date
     * @param CalculationResultInterface $delivery
     * @return bool
     */
    public function isSuitable(\DateTime $date, CalculationResultInterface $delivery): bool
    {
        /** Не применять для второй зоны с поставкой с другого склада */
        if (($delivery->getDeliveryZone() === DeliveryService::ZONE_2) &&
            (bool)$delivery->getShipmentResults()
        ) {
            $result = false;
        } else {
            $hour = $date->format('G');
            $to = ($this->getTo() === 0) ? 24 : $this->getTo();
            $result = ($hour >= $this->getFrom()) && ($hour < $to);
        }

        return $result;
    }

    /**
     * @param \DateTime $date
     *
     * @return \DateTime
     */
    public function apply(\DateTime $date): \DateTime
    {
        $result = clone $date;
        $currentDate = (new \DateTime())->setTime(
            $date->format('H'),
            $date->format('i'),
            $date->format('s'),
            $date->format('u')
        );

        $diff = $this->getValue() - $result->diff($currentDate)->days;
        if ($diff > 0) {
            $result->modify(sprintf('+%s days', $diff));
        }

        return $result;
    }
}
