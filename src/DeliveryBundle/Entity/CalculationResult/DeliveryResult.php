<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
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

        $this->deliveryDate = $this->getNextDeliveryDate($date);
        $date->modify(
            sprintf(
                '+%s days',
                (clone $this)->setSelectedInterval($this->getFirstInterval())->getIntervalOffset()
            )
        );
        $this->deliveryDate = $this->getNextDeliveryDate($date);
        return clone $this->deliveryDate;
    }

    /**
     * Кол-во дней, прибавляемых к дате доставки при применении правила интервала
     *
     * @throws ApplicationCreateException
     * @return int
     */
    public function getIntervalOffset(): int
    {
        if (null === $this->intervalOffset) {
            $this->intervalOffset = 0;
            if ($interval = $this->getSelectedInterval()) {
                $defaultDate = clone ($this->deliveryDate ?? $this->getCurrentDate());
                $date = clone $defaultDate;
                foreach ($interval->getRules() as $rule) {
                    if (!$rule instanceof TimeRuleInterface) {
                        continue;
                    }

                    if (!$rule->isSuitable($defaultDate, $this)) {
                        continue;
                    }

                    $date = $rule->apply($defaultDate);
                    break;
                }

                $this->intervalOffset = (clone $date)->setTime(0, 0, 0, 0)
                    ->diff((clone $defaultDate)->setTime(0, 0, 0, 0))
                    ->days;
            }
        }

        return $this->intervalOffset;
    }

    /**
     * @param \DateTime $date
     *
     * @return \DateTime
     */
    protected function getNextDeliveryDate(\DateTime $date): \DateTime
    {
        $date = clone $date;
        if ($availableDays = $this->getWeekDays()) {
            $deliveryDay = (int)$date->format('N');
            while (!\in_array($deliveryDay, $availableDays, true)) {
                $deliveryDay = (int)$date->modify('+1 day')->format('N');
            }
        }

        return $date;
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
