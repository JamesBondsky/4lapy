<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;
use FourPaws\DeliveryBundle\Entity\IntervalRule\TimeRuleInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;


class DeliveryResult extends BaseResult implements DeliveryResultInterface
{
    use DeliveryResultTrait;

    /**
     * @return int
     * @throws ArgumentException
     * @throws ApplicationCreateException
     * @throws StoreNotFoundException
     * @throws SystemException
     */
    public function getPeriodTo(): int
    {
        return $this->getPeriodFrom() + 10;
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws NotFoundException
     * @return \DateTime
     */
    public function getDeliveryDate(): \DateTime
    {
        $date = parent::getDeliveryDate();
        if (null === $this->intervalOffset) {
            $this->intervalOffset = $this->calculateIntervalOffset();
        }

        return (clone $date)->modify(sprintf('+%s days', $this->intervalOffset));
    }

    /**
     * @return int
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws NotFoundException
     */
    protected function calculateIntervalOffset(): int
    {
        $result = 0;

        if ($this->getIntervals()->isEmpty()) {
            return $result;
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
            return $result;
        }

        /** @var BaseRule $rule */
        $defaultDate = clone $this->deliveryDate;
        $date = clone $this->deliveryDate;
        foreach ($interval->getRules() as $rule) {
            if (!$rule instanceof TimeRuleInterface) {
                continue;
            }

            if (!$rule->isSuitable($defaultDate)) {
                continue;
            }

            $date = $rule->apply($defaultDate);
            break;
        }

        $result = $date->diff($defaultDate)->days;
        if ($this->getDateOffset() > 0) {
            $defaultOffset = 0;
            if ((null !== $firstInterval) && (string)$interval !== (string)$firstInterval) {
                $defaultOffset = $date->diff(
                    (clone $this)->setSelectedInterval($firstInterval)->getDeliveryDate()
                );
            }
            $diff = $result - $defaultOffset;
            $result = $this->getDateOffset() - $diff;
        }

        return $result;
    }

    /**
     * @param bool $internalCall
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     * @throws SystemException
     */
    public function isSuccess($internalCall = false)
    {
        return parent::isSuccess($internalCall);
    }

    /**
     * @param Offer $offer
     *
     * @return bool
     * @throws ApplicationCreateException
     */
    protected function checkIsDeliverable(Offer $offer): bool
    {
        return parent::checkIsDeliverable($offer) && $offer->getProduct()->isDeliveryAvailable();
    }

    protected function resetResult(): void
    {
        parent::resetResult();
        $this->selectedInterval = null;
    }
}
