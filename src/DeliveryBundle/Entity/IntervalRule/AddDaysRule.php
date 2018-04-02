<?php

namespace FourPaws\DeliveryBundle\Entity\IntervalRule;

use Bitrix\Main\ArgumentException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;

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
     * @param CalculationResultInterface $result
     *
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     * @return bool
     */
    public function isSuitable(CalculationResultInterface $result): bool
    {
        $hour = $result->getDeliveryDate()->format('G');

        $to = ($this->getTo() === 0) ? 24 : $this->getTo();
        return ($hour >= $this->getFrom()) && ($hour < $to);
    }

    /**
     * @param CalculationResultInterface $result
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     * @return CalculationResultInterface
     */
    public function apply(CalculationResultInterface $result): CalculationResultInterface
    {
        if (!$this->isSuitable($result)) {
            return $result;
        }

        if ($this->getValue() === 0) {
            return $result;
        }

        $result->getDeliveryDate()->modify(sprintf('+%s days', $this->getValue()));

        return $result;
    }
}
