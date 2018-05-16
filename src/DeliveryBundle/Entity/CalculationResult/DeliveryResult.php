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

        return (clone $date)->modify(sprintf('+%s days', $this->getFullOffset()));
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

                    if (!$rule->isSuitable($defaultDate)) {
                        continue;
                    }

                    $date = $rule->apply($defaultDate);
                    break;
                }

                $this->intervalOffset = $date->diff($defaultDate)->days;
            }
        }

        return $this->intervalOffset;
    }

    /**
     * Комбинирует выбранную дату доставки и результат применения правил интервалов
     *
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @return int
     */
    protected function getFullOffset(): int
    {
        $result = $this->getDateOffset();
        if (!$this->getIntervals()->isEmpty()) {
            $result += (clone $this)->setSelectedInterval($this->getFirstInterval())->getIntervalOffset();
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
