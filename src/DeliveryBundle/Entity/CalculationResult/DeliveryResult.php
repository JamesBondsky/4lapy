<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;
use FourPaws\DeliveryBundle\Entity\IntervalRule\TimeRuleInterface;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;


class DeliveryResult extends BaseResult
{
    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     */
    public function doCalculateDeliveryDate(): void
    {
        parent::doCalculateDeliveryDate();

        if ($this->getIntervals()->isEmpty()) {
            return;
        }

        /**
         * Расчет даты доставки с учетом правил интервалов
         */
        $interval = $this->getSelectedInterval();
        if (!$interval instanceof Interval) {
            return;
        }

        $date = clone $this->deliveryDate;
        /** @var BaseRule $rule */
        foreach ($interval->getRules() as $rule) {
            if (!$rule instanceof TimeRuleInterface) {
                continue;
            }

            if (!$rule->isSuitable($this)) {
                continue;
            }

            $rule->apply($this);
            break;
        }

        /**
         * кол-во дней, которое было добавлено при применении интервала
         */
        $addedDays = $this->deliveryDate->diff($date)->days;

        $diff = $this->getDateOffset() - $addedDays;
        if ($diff > 0) {
            $this->deliveryDate->modify(sprintf('+%s days', $diff));
        }
    }

    /**
     * @return int
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @throws StoreNotFoundException
     */
    public function getPeriodTo(): int
    {
        return $this->getPeriodFrom() + 10;
    }
}
