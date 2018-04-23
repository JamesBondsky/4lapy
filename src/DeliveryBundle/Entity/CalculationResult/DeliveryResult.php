<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;
use FourPaws\DeliveryBundle\Entity\IntervalRule\TimeRuleInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;


class DeliveryResult extends BaseResult
{
    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws NotFoundException
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
        $firstInterval = $this->getFirstInterval();
        if (null === $this->selectedInterval) {
            $interval = $firstInterval;
        } else {
            $interval = $this->selectedInterval;
        }
        if (!$interval instanceof Interval) {
            return;
        }

        /** @var BaseRule $rule */
        $date = clone $this->deliveryDate;
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

        if ($this->getDateOffset() > 0) {
            $defaultOffset = 0;
            if ((null !== $firstInterval) && (string)$interval !== (string)$firstInterval) {
                $defaultOffset = $date->diff(
                    (clone $this)->setSelectedInterval($firstInterval)->getDeliveryDate()
                );
            }
            $newOffset = $date->diff($this->deliveryDate)->days;
            $diff = $newOffset - $defaultOffset;
            $this->deliveryDate->modify(sprintf('+%s days', $this->getDateOffset() - $diff));
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
