<?php

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Entity\IntervalRule\TimeRuleInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
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
        $date = clone parent::getDeliveryDate();

        $date->modify(
            sprintf(
                '+%s days',
                $this->getFullOffset()
            )
        );
        $date = $this->getNextDeliveryDate($date);

        return clone $date;
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
                /**
                 * для всех зон, кроме 2, при поставке со склада поставщика
                 * должны быть доступны интервалы для 9:00 с даты доступности товара на РЦ
                 */
                if ((!in_array($this->getDeliveryZone(), DeliveryService::getZonesTwo())) && (bool)$this->getShipmentResults()) {
                    $defaultDate = clone $this->deliveryDate;
                } else {
                    $defaultDate = clone $this->currentDate;
                }

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

                $date->setTime(0, 0, 0, 0);
                $deliveryDate = (clone $this->deliveryDate)->setTime(0, 0, 0, 0);
                $defaultDate->setTime(0,0,0,0);

                $addedDays = $date->diff($defaultDate)->days;
                $deliveryDateDiff = $deliveryDate->diff($defaultDate)->days;

                $intervalOffset = $addedDays - $deliveryDateDiff;
                $this->intervalOffset = $intervalOffset > 0 ? $intervalOffset : 0;
            }
        }

        return $this->intervalOffset;
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

    /**
     * Возвращает отформатированный текст о доставке для карточки товара на сайте и в мобильном приложении
     * @param float $offerPrice
     * @param bool $isByRequest
     * @param bool $withCurrency
     * @return string
     */
    public function getTextForOffer(float $offerPrice, $isByRequest = false, $withCurrency = false): string
    {
        $text = DeliveryTimeHelper::showByDate($this->deliveryDate, 0, ['DATE_FORMAT' => 'XX']);
        if ($isByRequest) {
            $text .= ' ближайшая';
        } elseif ($this->freeFrom && $offerPrice > $this->freeFrom) {
            $text .= ' бесплатно ';
        } else if ($this->freeFrom) {
            $text .= ' бесплатно от ' . $this->freeFrom;
            if ($withCurrency) {
                $text .= $this->currency;
            }
        }
        return $text;
    }

    protected function resetResult(): void
    {
        parent::resetResult();
        $this->selectedInterval = null;
    }
}
